@extends('layouts.app')

@section('title', 'Détail du compte')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-university me-2"></i>Détail du compte</h2>
                <div>
                    @can('manage-cashflow-accounts')
                        <a href="{{ route('cashflow.accounts.edit', $account->accountId) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                    @endcan
                    <a href="{{ route('cashflow.accounts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-wallet stats-icon"></i>
                    <h3>{{ number_format($account->initialBalance, 2, ',', ' ') }} USD</h3>
                    <p>Solde initial</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-arrow-down stats-icon"></i>
                    <h3>{{ number_format($totalIncome, 2, ',', ' ') }} USD</h3>
                    <p>Total Entrées</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-arrow-up stats-icon"></i>
                    <h3>{{ number_format($totalExpense, 2, ',', ' ') }} USD</h3>
                    <p>Total Sorties</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card stats-card" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-balance-scale stats-icon"></i>
                    <h2>{{ number_format($account->currentBalance, 2, ',', ' ') }} USD</h2>
                    <p>Solde actuel</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du compte</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Nom</th>
                            <td>{{ $account->accountName }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                @if($account->accountType == 'cash')
                                    <span class="badge bg-info">Espèces</span>
                                @elseif($account->accountType == 'bank')
                                    <span class="badge bg-primary">Banque</span>
                                @else
                                    <span class="badge bg-success">Mobile Money</span>
                                @endif
                            </td>
                        </tr>
                        @if($account->description)
                        <tr>
                            <th>Description</th>
                            <td>{{ $account->description }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Statut</th>
                            <td>
                                @if($account->isActive)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Solde initial</th>
                            <td><strong>{{ number_format($account->initialBalance, 2, ',', ' ') }} USD</strong></td>
                        </tr>
                        <tr>
                            <th>Solde actuel</th>
                            <td>
                                <strong class="{{ $account->currentBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($account->currentBalance, 2, ',', ' ') }} USD
                                </strong>
                            </td>
                        </tr>
                    </table>

                    @can('manage-cashflow-accounts')
                        <div class="mt-3">
                            <form method="POST" action="{{ route('cashflow.accounts.recalculate', $account->accountId) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-sync me-2"></i>Recalculer le solde
                                </button>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Dernières transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($account->transactions->take(10) as $transaction)
                                <tr>
                                    <td>{{ $transaction->transactionDate->format('d/m/Y') }}</td>
                                    <td>
                                        @if($transaction->transactionType == 'income')
                                            <span class="badge bg-success">Entrée</span>
                                        @else
                                            <span class="badge bg-danger">Sortie</span>
                                        @endif
                                    </td>
                                    <td class="{{ $transaction->transactionType == 'income' ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ $transaction->transactionType == 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2, ',', ' ') }} USD</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Aucune transaction</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($account->transactions->count() > 10)
                        <div class="text-center mt-2">
                            <a href="{{ route('cashflow.index', ['accountId' => $account->accountId]) }}" class="btn btn-sm btn-outline-primary">
                                Voir toutes les transactions
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

