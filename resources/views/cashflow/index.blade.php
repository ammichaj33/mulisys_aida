@extends('layouts.app')

@section('title', 'Gestion de la trésorerie')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-wallet me-2"></i>Gestion de la trésorerie</h2>
                @can('create-cashflow')
                    <a href="{{ route('cashflow.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouvelle transaction
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('cashflow.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" 
                                   value="{{ request('startDate') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" 
                                   value="{{ request('endDate') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="transactionType" class="form-label">Type</label>
                            <select class="form-select" id="transactionType" name="transactionType">
                                <option value="">Tous</option>
                                <option value="income" {{ request('transactionType') == 'income' ? 'selected' : '' }}>Entrées</option>
                                <option value="expense" {{ request('transactionType') == 'expense' ? 'selected' : '' }}>Sorties</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="categoryId" class="form-label">Catégorie</label>
                            <select class="form-select" id="categoryId" name="categoryId">
                                <option value="">Toutes</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->categoryId }}" {{ request('categoryId') == $category->categoryId ? 'selected' : '' }}>
                                        {{ $category->categoryName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                                <a href="{{ route('cashflow.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Totaux -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-down stats-icon"></i>
                    <h3>{{ number_format($totalIncome, 2, ',', ' ') }} USD</h3>
                    <p>Total Entrées</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-up stats-icon"></i>
                    <h3>{{ number_format($totalExpense, 2, ',', ' ') }} USD</h3>
                    <p>Total Sorties</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-balance-scale stats-icon"></i>
                    <h3>{{ number_format($balance, 2, ',', ' ') }} USD</h3>
                    <p>Solde</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Liste des transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Catégorie</th>
                                    <th>Description</th>
                                    <th>Compte</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transactionDate->format('d/m/Y') }}</td>
                                    <td>
                                        @if($transaction->transactionType == 'income')
                                            <span class="badge bg-success">Entrée</span>
                                        @else
                                            <span class="badge bg-danger">Sortie</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->category->categoryName }}</td>
                                    <td>{{ Str::limit($transaction->description, 50) }}</td>
                                    <td>{{ $transaction->account->accountName ?? 'N/A' }}</td>
                                    <td class="{{ $transaction->transactionType == 'income' ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ $transaction->transactionType == 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2, ',', ' ') }} USD</strong>
                                    </td>
                                    <td>
                                        @if($transaction->status == 'confirmed')
                                            <span class="badge bg-success">Confirmé</span>
                                        @elseif($transaction->status == 'pending')
                                            <span class="badge bg-warning">En attente</span>
                                        @else
                                            <span class="badge bg-secondary">Annulé</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('cashflow.show', $transaction->cashflowTransactionId) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('edit-cashflow')
                                                @if($transaction->status != 'cancelled')
                                                    <a href="{{ route('cashflow.edit', $transaction->cashflowTransactionId) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('confirm-cashflow')
                                                @if($transaction->status == 'pending')
                                                    <form method="POST" action="{{ route('cashflow.confirm', $transaction->cashflowTransactionId) }}" 
                                                          style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Confirmer">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                            @can('edit-cashflow')
                                                @if($transaction->status != 'cancelled')
                                                    <form method="POST" action="{{ route('cashflow.cancel', $transaction->cashflowTransactionId) }}" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette transaction ?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Annuler">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                            @can('delete-cashflow')
                                                @if($transaction->status != 'confirmed')
                                                    <form method="POST" action="{{ route('cashflow.destroy', $transaction->cashflowTransactionId) }}" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Aucune transaction trouvée</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

