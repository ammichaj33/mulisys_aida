@extends('layouts.app')

@section('title', 'Comptes de trésorerie')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-university me-2"></i>Comptes de trésorerie</h2>
                <a href="{{ route('cashflow.accounts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau compte
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Solde initial</th>
                                    <th>Solde actuel</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $account)
                                <tr>
                                    <td>{{ $account->accountName }}</td>
                                    <td>
                                        @if($account->accountType == 'cash')
                                            <span class="badge bg-info">Espèces</span>
                                        @elseif($account->accountType == 'bank')
                                            <span class="badge bg-primary">Banque</span>
                                        @else
                                            <span class="badge bg-success">Mobile Money</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($account->initialBalance, 2, ',', ' ') }} USD</td>
                                    <td>
                                        <strong class="{{ $account->currentBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($account->currentBalance, 2, ',', ' ') }} USD
                                        </strong>
                                    </td>
                                    <td>
                                        @if($account->isActive)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-secondary">Inactif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('cashflow.accounts.show', $account->accountId) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('cashflow.accounts.edit', $account->accountId) }}" 
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('cashflow.accounts.recalculate', $account->accountId) }}" 
                                                  style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Recalculer le solde">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('cashflow.accounts.destroy', $account->accountId) }}" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Aucun compte</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

