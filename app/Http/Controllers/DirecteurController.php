<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanDoc;
use App\Models\Member;
use App\Models\LoanRepayment;
use App\Models\Penalty;
use App\Models\User;

class DirecteurController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-dashboard-directeur');
    }

    /**
     * Display the dashboard.
     */
    public function dashboard(Request $request)
    {
        // Statistiques générales
        $totalLoans = LoanDoc::count();
        $totalAmount = LoanDoc::sum('requestAmount');
        $totalRepaid = LoanRepayment::sum('amount');
        $outstandingAmount = $totalAmount - $totalRepaid;
        
        // Statistiques par mois
        $monthlyStats = [
            'this_month' => [
                'loans' => LoanDoc::whereMonth('createdAt', now()->month)->count(),
                'amount' => LoanDoc::whereMonth('createdAt', now()->month)->sum('requestAmount'),
                'repayments' => LoanRepayment::whereMonth('repaymentDate', now()->month)->sum('amount')
            ],
            'last_month' => [
                'loans' => LoanDoc::whereMonth('createdAt', now()->subMonth()->month)->count(),
                'amount' => LoanDoc::whereMonth('createdAt', now()->subMonth()->month)->sum('requestAmount'),
                'repayments' => LoanRepayment::whereMonth('repaymentDate', now()->subMonth()->month)->sum('amount')
            ]
        ];

        // Membres avec le plus de crédits
        $topMembers = Member::withCount('loanDocs')
            ->withSum('loanDocs', 'requestAmount')
            ->orderBy('loan_docs_sum_request_amount', 'desc')
            ->limit(5)
            ->get();

        // Crédits en retard
        $overdueLoans = LoanDoc::where('status', 'done')
            ->where('endedDate', '<', now())
            ->with('member')
            ->get();

        // Évolution des crédits (6 derniers mois)
        $loanEvolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $loanEvolution[] = [
                'month' => $month->format('M Y'),
                'loans' => LoanDoc::whereMonth('createdAt', $month->month)->count(),
                'amount' => LoanDoc::whereMonth('createdAt', $month->month)->sum('requestAmount')
            ];
        }

        return view('directeur.dashboard', compact(
            'totalLoans', 'totalAmount', 'totalRepaid', 'outstandingAmount',
            'monthlyStats', 'topMembers', 'overdueLoans', 'loanEvolution'
        ));
    }

    /**
     * Display loan reports.
     */
    public function reportsLoans(Request $request)
    {
        $query = LoanDoc::with('member');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('createdAt', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('createdAt', '<=', $request->date_to);
        }

        $loans = $query->orderBy('createdAt', 'desc')->paginate(20);

        return view('directeur.reports.loans', compact('loans'));
    }

    /**
     * Display interest reports.
     */
    public function reportsInterests(Request $request)
    {
        $query = LoanRepayment::with(['loanDoc.member', 'repaymentType']);

        if ($request->filled('date_from')) {
            $query->whereDate('repaymentDate', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('repaymentDate', '<=', $request->date_to);
        }

        $repayments = $query->orderBy('repaymentDate', 'desc')->paginate(20);

        $totalInterests = $repayments->sum('amount');

        return view('directeur.reports.interests', compact('repayments', 'totalInterests'));
    }
}
