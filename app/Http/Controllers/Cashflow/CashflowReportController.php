<?php

namespace App\Http\Controllers\Cashflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashflowTransaction;
use App\Models\CashflowCategory;
use App\Models\CashflowAccount;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class CashflowReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Cashflow par période
     */
    public function cashflowByPeriod(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', Carbon::now()->format('Y-m-d'));

        $query = CashflowTransaction::with(['category', 'account', 'loanDoc', 'member', 'user']);

        // Filtres
        if ($request->filled('startDate')) {
            $query->where('transactionDate', '>=', $request->startDate);
        } else {
            $query->where('transactionDate', '>=', $startDate);
        }

        if ($request->filled('endDate')) {
            $query->where('transactionDate', '<=', $request->endDate);
        } else {
            $query->where('transactionDate', '<=', $endDate);
        }

        if ($request->filled('transactionType')) {
            $query->where('transactionType', $request->transactionType);
        }

        if ($request->filled('categoryId')) {
            $query->where('categoryIdFk', $request->categoryId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Par défaut, seulement les transactions confirmées pour le rapport
            $query->where('status', 'confirmed');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('referenceNumber', 'like', "%{$search}%")
                  ->orWhereHas('member', function($memberQuery) use ($search) {
                      $memberQuery->where('firstName', 'like', "%{$search}%")
                                 ->orWhere('lastName', 'like', "%{$search}%");
                  })
                  ->orWhereHas('loanDoc', function($loanQuery) use ($search) {
                      $loanQuery->where('refNumber', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->orderBy('transactionDate')
            ->orderBy('createdAt')
            ->get();

        $totalIncome = $transactions->where('transactionType', 'income')->sum('amount');
        $totalExpense = $transactions->where('transactionType', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Récupérer les catégories pour le filtre
        $categories = CashflowCategory::where('isActive', true)->orderBy('categoryName')->get();

        // Préparer les filtres pour le PDF
        $filters = [
            'startDate' => $request->input('startDate', $startDate),
            'endDate' => $request->input('endDate', $endDate),
            'transactionType' => $request->input('transactionType'),
            'categoryId' => $request->input('categoryId'),
            'status' => $request->input('status', 'confirmed'),
            'search' => $request->input('search'),
        ];

        if ($request->input('pdf')) {
            return $this->generateCashflowByPeriodPDF($transactions, $startDate, $endDate, $totalIncome, $totalExpense, $balance, $filters, $categories);
        }

        return view('cashflow.reports.by-period', compact('transactions', 'startDate', 'endDate', 'totalIncome', 'totalExpense', 'balance', 'categories'));
    }

    /**
     * Cashflow par catégorie
     */
    public function cashflowByCategory(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', Carbon::now()->format('Y-m-d'));

        $categories = CashflowCategory::where('isActive', true)
            ->with(['transactions' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('transactionDate', [$startDate, $endDate])
                      ->where('status', 'confirmed');
            }])
            ->get()
            ->map(function($category) {
                $category->totalIncome = $category->transactions->where('transactionType', 'income')->sum('amount');
                $category->totalExpense = $category->transactions->where('transactionType', 'expense')->sum('amount');
                $category->total = $category->totalIncome - $category->totalExpense;
                return $category;
            })
            ->filter(function($category) {
                return $category->totalIncome > 0 || $category->totalExpense > 0;
            })
            ->sortByDesc(function($category) {
                return abs($category->total);
            });

        $totalIncome = $categories->sum('totalIncome');
        $totalExpense = $categories->sum('totalExpense');
        $balance = $totalIncome - $totalExpense;

        if ($request->input('pdf')) {
            return $this->generateCashflowByCategoryPDF($categories, $startDate, $endDate, $totalIncome, $totalExpense, $balance);
        }

        return view('cashflow.reports.by-category', compact('categories', 'startDate', 'endDate', 'totalIncome', 'totalExpense', 'balance'));
    }

    /**
     * Rapport de trésorerie (format comptable)
     */
    public function treasuryReport(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', Carbon::now()->format('Y-m-d'));

        // Solde initial (avant la période)
        $initialBalance = CashflowAccount::sum('initialBalance');
        $initialIncome = CashflowTransaction::where('transactionType', 'income')
            ->where('status', 'confirmed')
            ->where('transactionDate', '<', $startDate)
            ->sum('amount');
        $initialExpense = CashflowTransaction::where('transactionType', 'expense')
            ->where('status', 'confirmed')
            ->where('transactionDate', '<', $startDate)
            ->sum('amount');
        $openingBalance = $initialBalance + $initialIncome - $initialExpense;

        // Transactions de la période
        $periodIncome = CashflowTransaction::where('transactionType', 'income')
            ->where('status', 'confirmed')
            ->whereBetween('transactionDate', [$startDate, $endDate])
            ->sum('amount');

        $periodExpense = CashflowTransaction::where('transactionType', 'expense')
            ->where('status', 'confirmed')
            ->whereBetween('transactionDate', [$startDate, $endDate])
            ->sum('amount');

        $closingBalance = $openingBalance + $periodIncome - $periodExpense;

        // Par compte
        $accounts = CashflowAccount::where('isActive', true)->get()->map(function($account) use ($startDate, $endDate) {
            $account->openingBalance = $account->initialBalance;
            $account->periodIncome = $account->transactions()
                ->where('transactionType', 'income')
                ->where('status', 'confirmed')
                ->whereBetween('transactionDate', [$startDate, $endDate])
                ->sum('amount');
            $account->periodExpense = $account->transactions()
                ->where('transactionType', 'expense')
                ->where('status', 'confirmed')
                ->whereBetween('transactionDate', [$startDate, $endDate])
                ->sum('amount');
            $account->closingBalance = $account->openingBalance + $account->periodIncome - $account->periodExpense;
            return $account;
        });

        if ($request->input('pdf')) {
            return $this->generateTreasuryReportPDF($accounts, $startDate, $endDate, $openingBalance, $periodIncome, $periodExpense, $closingBalance);
        }

        return view('cashflow.reports.treasury', compact('accounts', 'startDate', 'endDate', 'openingBalance', 'periodIncome', 'periodExpense', 'closingBalance'));
    }

    /**
     * Générer le PDF pour cashflow par période
     */
    private function generateCashflowByPeriodPDF($transactions, $startDate, $endDate, $totalIncome, $totalExpense, $balance, $filters, $categories)
    {
        $user = Auth::user();
        $pdf = Pdf::loadView('cashflow.pdf.by-period', compact('transactions', 'startDate', 'endDate', 'totalIncome', 'totalExpense', 'balance', 'filters', 'categories', 'user'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('cashflow-par-periode-' . $startDate . '-' . $endDate . '.pdf');
    }

    /**
     * Générer le PDF pour cashflow par catégorie
     */
    private function generateCashflowByCategoryPDF($categories, $startDate, $endDate, $totalIncome, $totalExpense, $balance)
    {
        $pdf = Pdf::loadView('cashflow.pdf.by-category', compact('categories', 'startDate', 'endDate', 'totalIncome', 'totalExpense', 'balance'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('cashflow-par-categorie-' . $startDate . '-' . $endDate . '.pdf');
    }

    /**
     * Générer le PDF pour le rapport de trésorerie
     */
    private function generateTreasuryReportPDF($accounts, $startDate, $endDate, $openingBalance, $periodIncome, $periodExpense, $closingBalance)
    {
        $pdf = Pdf::loadView('cashflow.pdf.treasury', compact('accounts', 'startDate', 'endDate', 'openingBalance', 'periodIncome', 'periodExpense', 'closingBalance'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('rapport-tresorerie-' . $startDate . '-' . $endDate . '.pdf');
    }
}

