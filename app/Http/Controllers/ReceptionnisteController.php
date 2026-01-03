<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanDoc;
use App\Models\Member;
use App\Models\RepaymentType;
use App\Services\LoanCalculationService;

class ReceptionnisteController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-dashboard-receptionniste');
    }

    /**
     * Show the receptionniste dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        // Construction de la requête avec filtres
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
            'validated' => LoanDoc::where('status', 'validated')->count(),
            'done' => LoanDoc::where('status', 'done')->count(),
        ];

        return view('receptionniste.dashboard', compact('stats', 'recentRequests'));
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
            'draft' => LoanDoc::where('status', 'draft')->count(),
            'accepted' => LoanDoc::where('status', 'accepted')->count(),
            'validated' => LoanDoc::where('status', 'validated')->count(),
            'rejected' => LoanDoc::where('status', 'rejected')->count(),
            'done' => LoanDoc::where('status', 'done')->count(),
        ];

        return view('receptionniste.index', compact('loans', 'stats'));
    }

    /**
     * Show the form for creating a new loan request.
     */
    public function create()
    {
        $members = Member::where('isActive', true)->get();
        return view('receptionniste.create', compact('members'));
    }

    /**
     * Store a newly created loan request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'memberId' => 'required|exists:members,memberId',
            'requestAmount' => 'required|numeric|min:0',
            'loanMonths' => 'required|integer|min:1|max:36',
            'submitDate' => 'required|date',
            'description' => 'required|string|min:10|max:1000',
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        // Générer le numéro de référence
        $refNumber = 'MC' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Gérer l'upload du document
        $docPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = $refNumber . '_' . time();
            // Stocker en privé (disque local) pour empêcher l'accès direct public
            $docPath = $file->storeAs('documents', $fileName, 'local');
        }

        // Créer la demande
        $loanDoc = LoanDoc::create([
            'refNumber' => $refNumber,
            'submitDate' => $request->submitDate,
            'description' => $request->description,
            'status' => 'draft',
            'requestAmount' => $request->requestAmount,
            'loanMonths' => $request->loanMonths,
            'interestRate' => 10.00,
            'memberIdFk' => $request->memberId,
            'docPath' => $docPath,
        ]);

        return redirect()->route('receptionniste.dashboard')
            ->with('success', 'Demande de crédit enregistrée avec succès. Référence: ' . $refNumber);
    }

    /**
     * Display the specified loan request.
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
     * Show the form for editing the specified loan request.
     */
    public function edit($id)
    {
        $loan = LoanDoc::with('member')->findOrFail($id);
        $members = Member::where('isActive', true)->get();

        return view('receptionniste.edit', compact('loan', 'members'));
    }

    /**
     * Update the specified loan request.
     */
    public function update(Request $request, $id)
    {
        $loan = LoanDoc::findOrFail($id);

        $request->validate([
            'memberId' => 'required|exists:members,memberId',
            'requestAmount' => 'required|numeric|min:0',
            'loanMonths' => 'required|integer|min:1|max:36',
            'submitDate' => 'required|date',
            'description' => 'required|string|min:10|max:1000',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $data = [
            'memberIdFk' => $request->memberId,
            'requestAmount' => $request->requestAmount,
            'loanMonths' => $request->loanMonths,
            'submitDate' => $request->submitDate,
            'description' => $request->description,
        ];

        // Gérer l'upload du document si fourni
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = $loan->refNumber . '_' . time() . '_' . $file->getClientOriginalName();
            // Stocker en privé (disque local) pour empêcher l'accès direct public
            $data['docPath'] = $file->storeAs('documents', $fileName, 'local');
        }

        $loan->update($data);

        return redirect()->route('receptionniste.dashboard')
            ->with('success', 'Demande de crédit mise à jour avec succès.');
    }

    /**
     * Remove the specified loan request.
     */
    public function destroy($id)
    {
        $loanDoc = LoanDoc::findOrFail($id);
        $loanDoc->delete();

        return redirect()->route('receptionniste.dashboard')
            ->with('success', 'Demande de crédit supprimée avec succès.');
    }

    /**
     * Get members for search.
     */
    public function searchMembers(Request $request)
    {
        $query = $request->get('q');

        $members = Member::where('isActive', true)
            ->where(function($q) use ($query) {
                $q->where('firstName', 'like', "%{$query}%")
                  ->orWhere('lastName', 'like', "%{$query}%")
                  ->orWhere('phoneNumber', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($members);
    }
}
