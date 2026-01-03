<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanDoc;
use App\Models\Member;
use App\Services\LoanCalculationService;

class LoanController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
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

        return view('loans.index', compact('loans'));
    }

    /**
     * Display the specified resource.
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
     * Display the repayment schedule.
     */
    public function schedule($id)
    {
        $loan = LoanDoc::with(['member', 'loanRepayments'])->findOrFail($id);
        
        // Calculer l'échéancier de remboursement
        $schedule = $this->calculateRepaymentSchedule($loan);
        
        return view('loans.schedule', compact('loan', 'schedule'));
    }

    /**
     * Calculate repayment schedule.
     */
    private function calculateRepaymentSchedule($loan)
    {
        $amount = $loan->requestAmount;
        $months = $loan->loanMonths;
        $interestRate = $loan->interestRate;
        
        // Calcul dégressif des intérêts
        $totalInterest = $amount * ($interestRate / 100) * $months;
        $totalAmount = $amount + $totalInterest;
        
        $schedule = [];
        $remainingAmount = $totalAmount;
        $currentDate = $loan->submitDate;
        
        // Calculer les échéances mensuelles
        $monthlyPayment = $totalAmount / ceil($days / 30);
        
        for ($i = 1; $i <= ceil($days / 30); $i++) {
            $paymentDate = $currentDate->copy()->addMonths($i);
            $paymentAmount = min($monthlyPayment, $remainingAmount);
            
            $schedule[] = [
                'installment' => $i,
                'date' => $paymentDate,
                'amount' => $paymentAmount,
                'remaining' => $remainingAmount - $paymentAmount
            ];
            
            $remainingAmount -= $paymentAmount;
        }
        
        return $schedule;
    }

    /**
     * Servir le document d'un crédit de manière sécurisée
     */
    public function getDocument($id)
    {
        $loan = LoanDoc::findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit de voir les demandes de crédit
        if (!auth()->user()->can('view-loan-requests')) {
            abort(403, 'Accès non autorisé');
        }
        
        if (!$loan->docPath || !\Storage::disk('local')->exists($loan->docPath)) {
            abort(404, 'Document non trouvé');
        }
        
        $filePath = storage_path('app/' . $loan->docPath);
        $mimeType = \Storage::disk('local')->mimeType($loan->docPath);
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=3600'
        ]);
    }
}
