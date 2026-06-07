<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penalty;
use App\Models\LoanDoc;
use App\Services\PenaltyCalculationService;
use Illuminate\Support\Facades\Auth;

class PenaltyController extends Controller
{
    protected $penaltyService;

    public function __construct()
    {
        $this->penaltyService = new PenaltyCalculationService();
    }

    /**
     * Display a listing of penalties
     */
    public function index(Request $request)
    {
        $query = Penalty::with(['loanDoc.member']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by loan
        if ($request->filled('loan_id')) {
            $query->where('loanDocIdFk', $request->loan_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('loanDoc', function($loanQuery) use ($search) {
                      $loanQuery->where('refNumber', 'like', "%{$search}%")
                               ->orWhereHas('member', function($memberQuery) use ($search) {
                                   $memberQuery->where('firstName', 'like', "%{$search}%")
                                              ->orWhere('lastName', 'like', "%{$search}%");
                               });
                  });
            });
        }

        $penalties = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => Penalty::count(),
            'pending' => Penalty::where('status', 'notPaid')->count(),
            'paid' => Penalty::where('status', 'paid')->count(),
            'total_amount' => Penalty::where('status', 'notPaid')->sum('amount'),
            'paid_amount' => Penalty::where('status', 'paid')->sum('amount'),
        ];

        return view('penalties.index', compact('penalties', 'stats'));
    }

    /**
     * Show penalty details
     */
    public function show($id)
    {
        $penalty = Penalty::with(['loanDoc.member', 'loanDoc.loanRepayments'])->findOrFail($id);
        
        $penaltyDetails = $this->penaltyService->getPenaltyDetailsForDisplay($penalty);
        
        return view('penalties.show', compact('penalty', 'penaltyDetails'));
    }

    /**
     * Pay a penalty
     */
    public function pay(Request $request, $id)
    {
        $penalty = Penalty::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $penalty->amount,
            'payment_date' => 'required|date',
        ]);

        if ($penalty->status === 'paid') {
            return back()->with('error', 'Cette pénalité a déjà été payée.');
        }

        try {
            $result = $this->penaltyService->payPenalty(
                $id,
                $request->amount,
                $request->payment_date
            );

            if (!empty($result['success'])) {
                if (!empty($result['fully_paid'])) {
                    return back()->with('success', 'Pénalité payée intégralement.');
                }

                return back()->with('success', 'Paiement partiel enregistré. Reste à payer : '
                    . number_format($result['remaining'], 2) . ' USD');
            }

            return back()->with('error', 'Erreur lors du paiement de la pénalité.');
        } catch (\Exception $e) {
            \Log::error('Erreur paiement pénalité', [
                'penaltyId' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erreur lors du paiement : ' . $e->getMessage());
        }
    }

    /**
     * Calculate penalties for a specific loan (create missing notPaid only)
     */
    public function calculateForLoan($loanId)
    {
        $loan = LoanDoc::with(['member', 'loanRepayments'])->findOrFail($loanId);
        $penaltyAmount = $this->penaltyService->calculateLoanPenalty($loan);

        return $this->penaltyActionResponse(
            $penaltyAmount > 0
                ? 'Pénalités calculées: ' . number_format($penaltyAmount, 2) . ' USD'
                : 'Aucune nouvelle pénalité à calculer pour ce crédit.',
            $penaltyAmount > 0 ? 'success' : 'info'
        );
    }

    /**
     * Calculate all penalties (create missing notPaid only)
     */
    public function calculateAll()
    {
        $totalPenalties = $this->penaltyService->calculateAllPenalties();

        return $this->penaltyActionResponse(
            "Pénalités calculées pour {$totalPenalties} crédit(s).",
            'success'
        );
    }

    /**
     * Recalculate penalties for a specific loan (create or update notPaid)
     */
    public function recalculateForLoan($loanId)
    {
        $loan = LoanDoc::with(['member', 'loanRepayments'])->findOrFail($loanId);
        $result = $this->penaltyService->recalculateLoanPenalties($loan);

        return $this->penaltyActionResponse($this->formatRecalculateMessage($result));
    }

    /**
     * Recalculate all penalties (create or update notPaid)
     */
    public function recalculateAll()
    {
        $result = $this->penaltyService->recalculateAllPenalties();

        return $this->penaltyActionResponse($this->formatRecalculateMessage($result));
    }

    private function formatRecalculateMessage(array $result): string
    {
        if ($result['created'] === 0 && $result['updated'] === 0) {
            return 'Aucune pénalité à recalculer.';
        }

        $message = 'Recalcul terminé : '
            . $result['created'] . ' créée(s), '
            . $result['updated'] . ' mise(s) à jour, '
            . 'total ' . number_format($result['total_amount'], 2) . ' USD';

        if (isset($result['loans'])) {
            $message .= ' sur ' . $result['loans'] . ' crédit(s).';
        } else {
            $message .= '.';
        }

        return $message;
    }

    private function penaltyActionResponse(string $message, string $flashType = 'success')
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => $flashType !== 'error',
                'message' => $message,
            ]);
        }

        return back()->with($flashType, $message);
    }
}
