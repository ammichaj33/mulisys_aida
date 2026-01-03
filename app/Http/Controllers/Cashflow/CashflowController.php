<?php

namespace App\Http\Controllers\Cashflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashflowTransaction;
use App\Models\CashflowCategory;
use App\Models\CashflowAccount;
use App\Models\LoanDoc;
use App\Models\Member;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class CashflowController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $query = CashflowTransaction::with(['category', 'account', 'loanDoc', 'member', 'user']);

        // Filtres
        if ($request->filled('startDate')) {
            $query->where('transactionDate', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->where('transactionDate', '<=', $request->endDate);
        }

        if ($request->filled('transactionType')) {
            $query->where('transactionType', $request->transactionType);
        }

        if ($request->filled('categoryId')) {
            $query->where('categoryIdFk', $request->categoryId);
        }

        if ($request->filled('accountId')) {
            $query->where('accountIdFk', $request->accountId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $transactions = $query->orderBy('transactionDate', 'desc')
                             ->orderBy('createdAt', 'desc')
                             ->paginate(20);

        $categories = CashflowCategory::where('isActive', true)->orderBy('categoryName')->get();
        $accounts = CashflowAccount::where('isActive', true)->orderBy('accountName')->get();

        // Calculer les totaux
        $totalIncome = CashflowTransaction::where('transactionType', 'income')
            ->where('status', 'confirmed')
            ->when($request->filled('startDate'), function($q) use ($request) {
                $q->where('transactionDate', '>=', $request->startDate);
            })
            ->when($request->filled('endDate'), function($q) use ($request) {
                $q->where('transactionDate', '<=', $request->endDate);
            })
            ->sum('amount');

        $totalExpense = CashflowTransaction::where('transactionType', 'expense')
            ->where('status', 'confirmed')
            ->when($request->filled('startDate'), function($q) use ($request) {
                $q->where('transactionDate', '>=', $request->startDate);
            })
            ->when($request->filled('endDate'), function($q) use ($request) {
                $q->where('transactionDate', '<=', $request->endDate);
            })
            ->sum('amount');

        $balance = $totalIncome - $totalExpense;

        return view('cashflow.index', compact('transactions', 'categories', 'accounts', 'totalIncome', 'totalExpense', 'balance'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Request $request)
    {
        $categories = CashflowCategory::where('isActive', true)
            ->orderBy('categoryType')
            ->orderBy('categoryName')
            ->get();
        
        $accounts = CashflowAccount::where('isActive', true)->orderBy('accountName')->get();
        $loans = LoanDoc::whereIn('status', ['validated', 'done'])
            ->with('member')
            ->orderBy('refNumber')
            ->get();
        
        $members = Member::orderBy('firstName')->orderBy('lastName')->get();

        // Pré-remplir depuis un crédit si fourni
        $selectedLoan = null;
        if ($request->filled('loanId')) {
            $selectedLoan = LoanDoc::with('member')->find($request->loanId);
        }

        return view('cashflow.create', compact('categories', 'accounts', 'loans', 'members', 'selectedLoan'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'transactionDate' => 'required|date',
            'transactionType' => 'required|in:income,expense',
            'categoryIdFk' => 'required|exists:cashflow_categories,categoryId',
            'accountIdFk' => 'nullable|exists:cashflow_accounts,accountId',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'paymentMethod' => 'required|in:cash,bank,mobile_money,check',
            'referenceNumber' => 'nullable|string|max:100',
            'loanDocIdFk' => 'nullable|exists:loandocs,loanDocId',
            'memberIdFk' => 'nullable|exists:members,memberId',
            'status' => 'nullable|in:pending,confirmed,cancelled',
        ]);

        $transaction = CashflowTransaction::create([
            'transactionDate' => $request->transactionDate,
            'transactionType' => $request->transactionType,
            'categoryIdFk' => $request->categoryIdFk,
            'accountIdFk' => $request->accountIdFk,
            'amount' => $request->amount,
            'description' => $request->description,
            'paymentMethod' => $request->paymentMethod,
            'referenceNumber' => $request->referenceNumber,
            'loanDocIdFk' => $request->loanDocIdFk,
            'memberIdFk' => $request->memberIdFk,
            'userIdFk' => Auth::id(),
            'status' => $request->status ?? 'confirmed',
            'confirmedAt' => $request->status == 'confirmed' ? now() : null,
            'confirmedBy' => $request->status == 'confirmed' ? Auth::id() : null,
        ]);

        // Mettre à jour le solde du compte si fourni
        if ($transaction->accountIdFk && $transaction->status == 'confirmed') {
            $account = CashflowAccount::find($transaction->accountIdFk);
            if ($account) {
                $account->updateBalance();
            }
        }

        return redirect()->route('cashflow.index')
            ->with('success', 'Transaction enregistrée avec succès.');
    }

    /**
     * Display the specified transaction.
     */
    public function show($id)
    {
        $transaction = CashflowTransaction::with(['category', 'account', 'loanDoc.member', 'member', 'user', 'confirmedByUser'])
            ->findOrFail($id);

        return view('cashflow.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit($id)
    {
        $transaction = CashflowTransaction::findOrFail($id);
        
        // Vérifier que la transaction peut être modifiée
        if ($transaction->status == 'cancelled') {
            return redirect()->route('cashflow.index')
                ->withErrors(['error' => 'Une transaction annulée ne peut pas être modifiée.']);
        }

        $categories = CashflowCategory::where('isActive', true)
            ->orderBy('categoryType')
            ->orderBy('categoryName')
            ->get();
        
        $accounts = CashflowAccount::where('isActive', true)->orderBy('accountName')->get();
        $loans = LoanDoc::whereIn('status', ['validated', 'done'])
            ->with('member')
            ->orderBy('refNumber')
            ->get();
        
        $members = Member::orderBy('firstName')->orderBy('lastName')->get();

        return view('cashflow.edit', compact('transaction', 'categories', 'accounts', 'loans', 'members'));
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, $id)
    {
        $transaction = CashflowTransaction::findOrFail($id);

        // Vérifier que la transaction peut être modifiée
        if ($transaction->status == 'cancelled') {
            return back()->withErrors(['error' => 'Une transaction annulée ne peut pas être modifiée.']);
        }

        $request->validate([
            'transactionDate' => 'required|date',
            'transactionType' => 'required|in:income,expense',
            'categoryIdFk' => 'required|exists:cashflow_categories,categoryId',
            'accountIdFk' => 'nullable|exists:cashflow_accounts,accountId',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'paymentMethod' => 'required|in:cash,bank,mobile_money,check',
            'referenceNumber' => 'nullable|string|max:100',
            'loanDocIdFk' => 'nullable|exists:loandocs,loanDocId',
            'memberIdFk' => 'nullable|exists:members,memberId',
            'status' => 'nullable|in:pending,confirmed,cancelled',
        ]);

        $oldAccountId = $transaction->accountIdFk;
        $oldStatus = $transaction->status;

        $transaction->update([
            'transactionDate' => $request->transactionDate,
            'transactionType' => $request->transactionType,
            'categoryIdFk' => $request->categoryIdFk,
            'accountIdFk' => $request->accountIdFk,
            'amount' => $request->amount,
            'description' => $request->description,
            'paymentMethod' => $request->paymentMethod,
            'referenceNumber' => $request->referenceNumber,
            'loanDocIdFk' => $request->loanDocIdFk,
            'memberIdFk' => $request->memberIdFk,
            'status' => $request->status ?? $transaction->status,
            'confirmedAt' => $request->status == 'confirmed' ? ($transaction->confirmedAt ?? now()) : null,
            'confirmedBy' => $request->status == 'confirmed' ? ($transaction->confirmedBy ?? Auth::id()) : null,
        ]);

        // Mettre à jour les soldes des comptes affectés
        if ($oldAccountId) {
            $oldAccount = CashflowAccount::find($oldAccountId);
            if ($oldAccount) {
                $oldAccount->updateBalance();
            }
        }
        if ($transaction->accountIdFk && $transaction->status == 'confirmed') {
            $account = CashflowAccount::find($transaction->accountIdFk);
            if ($account) {
                $account->updateBalance();
            }
        }

        return redirect()->route('cashflow.index')
            ->with('success', 'Transaction modifiée avec succès.');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy($id)
    {
        $transaction = CashflowTransaction::findOrFail($id);

        // Vérifier que la transaction peut être supprimée
        if ($transaction->status == 'confirmed') {
            return back()->withErrors(['error' => 'Une transaction confirmée ne peut pas être supprimée. Annulez-la d\'abord.']);
        }

        $accountId = $transaction->accountIdFk;
        $transaction->delete();

        // Mettre à jour le solde du compte
        if ($accountId) {
            $account = CashflowAccount::find($accountId);
            if ($account) {
                $account->updateBalance();
            }
        }

        return redirect()->route('cashflow.index')
            ->with('success', 'Transaction supprimée avec succès.');
    }

    /**
     * Confirm a transaction.
     */
    public function confirm($id)
    {
        $transaction = CashflowTransaction::findOrFail($id);

        if ($transaction->status == 'confirmed') {
            return back()->withErrors(['error' => 'Cette transaction est déjà confirmée.']);
        }

        $transaction->update([
            'status' => 'confirmed',
            'confirmedAt' => now(),
            'confirmedBy' => Auth::id(),
        ]);

        // Mettre à jour le solde du compte
        if ($transaction->accountIdFk) {
            $account = CashflowAccount::find($transaction->accountIdFk);
            if ($account) {
                $account->updateBalance();
            }
        }

        return back()->with('success', 'Transaction confirmée avec succès.');
    }

    /**
     * Cancel a transaction.
     */
    public function cancel($id)
    {
        $transaction = CashflowTransaction::findOrFail($id);

        if ($transaction->status == 'cancelled') {
            return back()->withErrors(['error' => 'Cette transaction est déjà annulée.']);
        }

        $accountId = $transaction->accountIdFk;
        $transaction->update([
            'status' => 'cancelled',
        ]);

        // Mettre à jour le solde du compte
        if ($accountId) {
            $account = CashflowAccount::find($accountId);
            if ($account) {
                $account->updateBalance();
            }
        }

        return back()->with('success', 'Transaction annulée avec succès.');
    }
}

