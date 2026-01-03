<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanRepayment;
use App\Models\LoanDoc;
use App\Models\RepaymentType;
use Illuminate\Support\Facades\Auth;

class RepaymentController extends Controller
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
        $query = LoanRepayment::with(['loanDoc.member', 'repaymentType', 'user']);

        // Filtres
        if ($request->filled('date_from')) {
            $query->whereDate('repaymentDate', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('repaymentDate', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('loanDoc', function($loanQuery) use ($search) {
                    $loanQuery->where('refNumber', 'like', "%{$search}%")
                             ->orWhereHas('member', function($memberQuery) use ($search) {
                                 $memberQuery->where('firstName', 'like', "%{$search}%")
                                            ->orWhere('lastName', 'like', "%{$search}%")
                                            ->orWhere('phoneNumber', 'like', "%{$search}%");
                             });
                });
            });
        }

        $repayments = $query->orderBy('repaymentDate', 'desc')->paginate(20);

        return view('repayments.index', compact('repayments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $loans = LoanDoc::where('status', 'done')->with('member')->get();
        $repaymentTypes = RepaymentType::where('isActive', true)->get();
        
        return view('repayments.create', compact('loans', 'repaymentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'repaymentDate' => 'required|date',
            'repaymentTypeIdFk' => 'required|exists:repaymenttype,repaymentTypeID',
            'loanDocIdFk' => 'required|exists:loandocs,loanDocId',
            'description' => 'nullable|string|max:500'
        ]);

        $repayment = LoanRepayment::create([
            'amount' => $request->amount,
            'loanDocIdFk' => $request->loanDocIdFk,
            'repaymentDate' => $request->repaymentDate,
            'repaymentTypeIdFk' => $request->repaymentTypeIdFk,
            'userIdFk' => Auth::id()
        ]);

        return redirect()->route('repayments.index')
            ->with('success', 'Remboursement enregistré avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $repayment = LoanRepayment::with(['loanDoc.member', 'repaymentType', 'user'])->findOrFail($id);
        
        return view('repayments.show', compact('repayment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $repayment = LoanRepayment::findOrFail($id);
        $loans = LoanDoc::where('status', 'done')->with('member')->get();
        $repaymentTypes = RepaymentType::where('isActive', true)->get();
        
        return view('repayments.edit', compact('repayment', 'loans', 'repaymentTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $repayment = LoanRepayment::findOrFail($id);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'repaymentDate' => 'required|date',
            'repaymentTypeIdFk' => 'required|exists:repaymenttype,repaymentTypeID',
            'loanDocIdFk' => 'required|exists:loandocs,loanDocId',
            'description' => 'nullable|string|max:500'
        ]);

        $repayment->update($request->all());

        return redirect()->route('repayments.index')
            ->with('success', 'Remboursement mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $repayment = LoanRepayment::findOrFail($id);
        $repayment->delete();

        return redirect()->route('repayments.index')
            ->with('success', 'Remboursement supprimé avec succès.');
    }
}
