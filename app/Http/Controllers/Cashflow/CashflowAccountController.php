<?php

namespace App\Http\Controllers\Cashflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashflowAccount;

class CashflowAccountController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of accounts.
     */
    public function index()
    {
        $accounts = CashflowAccount::orderBy('accountName')->get();

        return view('cashflow.accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new account.
     */
    public function create()
    {
        return view('cashflow.accounts.create');
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'accountName' => 'required|string|max:100',
            'accountType' => 'required|in:cash,bank,mobile_money',
            'initialBalance' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $account = CashflowAccount::create([
            'accountName' => $request->accountName,
            'accountType' => $request->accountType,
            'initialBalance' => $request->initialBalance,
            'currentBalance' => $request->initialBalance,
            'description' => $request->description,
            'isActive' => true,
        ]);

        return redirect()->route('cashflow.accounts.index')
            ->with('success', 'Compte créé avec succès.');
    }

    /**
     * Display the specified account.
     */
    public function show($id)
    {
        $account = CashflowAccount::with('transactions.category', 'transactions.user')
            ->findOrFail($id);

        // Calculer les statistiques
        $totalIncome = $account->transactions()
            ->where('transactionType', 'income')
            ->where('status', 'confirmed')
            ->sum('amount');

        $totalExpense = $account->transactions()
            ->where('transactionType', 'expense')
            ->where('status', 'confirmed')
            ->sum('amount');

        return view('cashflow.accounts.show', compact('account', 'totalIncome', 'totalExpense'));
    }

    /**
     * Show the form for editing the specified account.
     */
    public function edit($id)
    {
        $account = CashflowAccount::findOrFail($id);

        return view('cashflow.accounts.edit', compact('account'));
    }

    /**
     * Update the specified account.
     */
    public function update(Request $request, $id)
    {
        $account = CashflowAccount::findOrFail($id);

        $request->validate([
            'accountName' => 'required|string|max:100',
            'accountType' => 'required|in:cash,bank,mobile_money',
            'initialBalance' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'isActive' => 'nullable|boolean',
        ]);

        $account->update([
            'accountName' => $request->accountName,
            'accountType' => $request->accountType,
            'initialBalance' => $request->initialBalance,
            'description' => $request->description,
            'isActive' => $request->has('isActive') ? $request->isActive : $account->isActive,
        ]);

        // Recalculer le solde actuel
        $account->updateBalance();

        return redirect()->route('cashflow.accounts.index')
            ->with('success', 'Compte modifié avec succès.');
    }

    /**
     * Remove the specified account.
     */
    public function destroy($id)
    {
        $account = CashflowAccount::findOrFail($id);

        // Vérifier si le compte est utilisé
        if ($account->transactions()->count() > 0) {
            return back()->withErrors(['error' => 'Ce compte ne peut pas être supprimé car il contient des transactions.']);
        }

        $account->delete();

        return redirect()->route('cashflow.accounts.index')
            ->with('success', 'Compte supprimé avec succès.');
    }

    /**
     * Recalculate account balance.
     */
    public function recalculateBalance($id)
    {
        $account = CashflowAccount::findOrFail($id);
        $account->updateBalance();

        return back()->with('success', 'Solde recalculé avec succès.');
    }
}

