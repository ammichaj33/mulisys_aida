<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanDoc;
use App\Models\Member;
use App\Models\Historique;
use App\Services\LoanCalculationService;

class ChargeCreditsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-dashboard-charge-credits');
    }

    /**
     * Display a listing of loan requests.
     */
    public function index(Request $request)
    {
        $query = LoanDoc::with('member');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('refNumber', 'like', "%{$search}%")
                  ->orWhereHas('member', function($memberQuery) use ($search) {
                      $memberQuery->where('firstName', 'like', "%{$search}%")
                                 ->orWhere('lastName', 'like', "%{$search}%")
                                 ->orWhere('phoneNumber', 'like', "%{$search}%");
                  });
            });
        }

        $loans = $query->orderBy('createdAt', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total' => LoanDoc::count(),
            'draft' => LoanDoc::where('status', 'draft')->count(),
            'accepted' => LoanDoc::where('status', 'accepted')->count(),
            'rejected' => LoanDoc::where('status', 'rejected')->count(),
            'validated' => LoanDoc::where('status', 'validated')->count(),
            'done' => LoanDoc::where('status', 'done')->count(),
        ];

        return view('charge_credits.index', compact('loans', 'stats'));
    }

    /**
     * Display the dashboard.
     */
    public function dashboard(Request $request)
    {
        $query = LoanDoc::with('member');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('refNumber', 'like', "%{$search}%")
                  ->orWhereHas('member', function($memberQuery) use ($search) {
                      $memberQuery->where('firstName', 'like', "%{$search}%")
                                 ->orWhere('lastName', 'like', "%{$search}%")
                                 ->orWhere('phoneNumber', 'like', "%{$search}%");
                  });
            });
        }

        $recentRequests = $query->orderBy('createdAt', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'draft' => LoanDoc::where('status', 'draft')->count(),
            'accepted' => LoanDoc::where('status', 'accepted')->count(),
            'rejected' => LoanDoc::where('status', 'rejected')->count(),
            'validated' => LoanDoc::where('status', 'validated')->count(),
            'done' => LoanDoc::where('status', 'done')->count(),
            'toreviewed' => LoanDoc::where('status', 'toreviewed')->count(),
        ];

        return view('charge_credits.dashboard', compact('recentRequests', 'stats'));
    }

    /**
     * Display validation page for pending loans.
     */
    public function validation()
    {
        // Récupérer les demandes en attente de validation (statut draft)
        $pendingLoans = LoanDoc::with('member')
            ->where('status', 'draft')
            ->orderBy('createdAt', 'asc')
            ->get();

        return view('charge_credits.validation', compact('pendingLoans'));
    }

    /**
     * Display the specified loan.
     */
    public function show($id)
    {
        $loan = LoanDoc::with([
            'member',
            'loanRepayments.repaymentType',
            'loanRepayments.user',
            'penalties',
            'validationHistory.user'
        ])->findOrFail($id);

        // Calculs financiers
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );
        
        $totalCancelledInterest = $calculationService->getCancelledInterest($loan->loanDocId);
        $totalAmountDue = $loan->requestAmount + $interestCalculation['total_interest'] - $totalCancelledInterest;
        
        // Récupérer les intérêts annulés détaillés
        $cancelledInterests = \App\Models\InterestCancellation::where('loanDocIdFk', $loan->loanDocId)->get();
        
        // Calculer le total remboursé et le montant restant
        $totalRepaid = $loan->loanRepayments->sum('amount');
        $remainingAmount = $totalAmountDue - $totalRepaid;

        return view('loans.show', compact('loan', 'interestCalculation', 'totalCancelledInterest', 'cancelledInterests', 'totalAmountDue', 'totalRepaid', 'remainingAmount', 'calculationService'));
    }

    /**
     * Display the validation form for a specific loan.
     */
    public function validateForm($id)
    {
        $loan = LoanDoc::with('member')->findOrFail($id);

        // Vérifier que la demande peut être validée (statut = draft)
        if ($loan->status != 'draft') {
            return redirect()->route('charge_credits.dashboard')
                ->with('error', 'Cette demande ne peut plus être validée car elle a déjà été traitée.');
        }

        // Calculs financiers
        $calculationService = new LoanCalculationService();
        $interestCalculation = $calculationService->calculateMonthlyDegressiveInterest(
            $loan->requestAmount,
            $loan->interestRate,
            $loan->loanMonths,
            $loan->submitDate->format('Y-m-d')
        );

        return view('charge_credits.validate-form', compact('loan', 'interestCalculation', 'calculationService'));
    }

    /**
     * Validate a loan request.
     */
    public function validateLoan(Request $request, $id)
    {
        $loan = LoanDoc::findOrFail($id);
        
        $request->validate([
            'decision' => 'required|in:accepted,rejected,toreviewed',
            'comments' => 'nullable|string|max:500'
        ]);

        $decision = $request->decision;
        $comments = $request->comments;
        
        // Mettre à jour le statut
        $loan->update(['status' => $decision]);
        
        // Logger l'opération
        $statusLabels = [
            'accepted' => 'Accepté',
            'rejected' => 'Rejeté', 
            'toreviewed' => 'À réviser'
        ];
        
        $description = "Validation par le chargé des crédits: " . $statusLabels[$decision];
        if ($comments) {
            $description .= " - Commentaires: " . $comments;
        }
        
        Historique::create([
            'recordIdFk' => $loan->loanDocId,
            'recordStatus' => $decision,
            'operDescription' => $description,
            'userIdFk' => Auth::id()
        ]);
        
        // TODO: Envoyer SMS de notification
        // $message = "Bonjour " . $loan->member->firstName . ", votre demande de crédit " . $loan->refNumber . " a été " . strtolower($statusLabels[$decision]) . ".";
        // sendSMS($loan->member->phoneNumber, $message);
        
        $successMessage = 'Validation enregistrée avec succès';
        
        return redirect()->route('charge_credits.dashboard')
            ->with('success', $successMessage);
    }
}
