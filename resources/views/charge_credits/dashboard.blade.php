@extends('layouts.app')

@section('title', 'Tableau de bord - Chargé des Crédits')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord - Chargé des Crédits</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('members.index') }}" class="btn btn-outline-info">
                    <i class="fas fa-users me-2"></i>Liste membres
                </a>
                <a href="{{ route('charge_credits.validation') }}" class="btn btn-primary">
                    <i class="fas fa-check-circle me-2"></i>Valider les demandes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Messages informatifs permanents -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Rôle :</strong> Chargé des Crédits - Vous êtes responsable de l'évaluation et de l'acceptation des demandes de crédit.
        </div>
    </div>
</div>


<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="{{ route('charge_credits.dashboard', ['status' => 'draft']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'draft') border-warning @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['draft'] }}</h3>
                        <p class="mb-0">Valider les demandes</p>
                    </div>
                    <i class="fas fa-edit stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('charge_credits.dashboard', ['status' => 'accepted']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'accepted') border-info @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['accepted'] }}</h3>
                        <p class="mb-0">Acceptés</p>
                    </div>
                    <i class="fas fa-check-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('charge_credits.dashboard', ['status' => 'rejected']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'rejected') border-danger @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                        <p class="mb-0">Rejetés</p>
                    </div>
                    <i class="fas fa-times-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('charge_credits.dashboard', ['status' => 'done']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'done') border-warning @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['done'] }}</h3>
                        <p class="mb-0">Terminés</p>
                    </div>
                    <i class="fas fa-flag-checkered stats-icon"></i>
                </div>
            </div>
        </a>
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
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            @foreach (['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser'] as $key => $label)
                                <option value="{{ $key }}" {{ (request('status') == $key) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Référence, nom, téléphone...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="{{ route('charge_credits.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Demandes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Demandes
                    @if ($recentRequests->total() > 0)
                        <span class="badge bg-primary ms-2">{{ $recentRequests->total() }} résultat(s)</span>
                    @endif
                </h5>
                @if (!empty(request('status')) || !empty(request('search')))
                    <div class="text-muted">
                        <small>Filtres actifs</small>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if ($recentRequests->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune demande à traiter</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Membre</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentRequests as $request)
                                    <tr @if($request->status == 'draft') class="table-danger" @endif>
                                        <td>
                                            <strong>{{ htmlspecialchars($request->refNumber) }}</strong>
                                        </td>
                                        <td>
                                            {{ htmlspecialchars($request->member->firstName . ' ' . $request->member->lastName) }}
                                            <br>
                                            <small class="text-muted">{{ htmlspecialchars($request->member->phoneNumber) }}</small>
                                        </td>
                                        <td>{{ number_format($request->requestAmount, 2, ',', ' ') }} USD</td>
                                        <td>
                                            @php
                                                $statusClass = '';
                                                switch ($request->status) {
                                                    case 'draft': $statusClass = 'bg-danger'; break;
                                                    case 'accepted': $statusClass = 'bg-success'; break;
                                                    case 'rejected': $statusClass = 'bg-danger'; break;
                                                    case 'validated': $statusClass = 'bg-primary'; break;
                                                    case 'done': $statusClass = 'bg-info'; break;
                                                    case 'toreviewed': $statusClass = 'bg-warning'; break;
                                                }
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ ['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser'][$request->status] }}
                                            </span>
                                        </td>
                                        <td>{{ date('d/m/Y', strtotime($request->submitDate)) }}</td>
                                        <td>
                                            <a href="{{ route('charge_credits.loans.show', $request->loanDocId) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($request->status == 'accepted')
                                                <a href="{{ route('charge_credits.loans.validate-form', $request->loanDocId) }}" 
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            
            <!-- Pagination -->
            @if ($recentRequests->hasPages())
                <div class="card-footer">
                    <nav aria-label="Pagination des demandes">
                        {{ $recentRequests->links() }}
                        
                        <!-- Informations de pagination -->
                        <div class="text-center text-muted">
                            <small>
                                Page {{ $recentRequests->currentPage() }} sur {{ $recentRequests->lastPage() }} 
                                ({{ $recentRequests->total() }} demande(s) au total)
                            </small>
                        </div>
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.stats-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stats-card.border-success {
    border-color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.05);
}

.stats-card.border-info {
    border-color: #0dcaf0 !important;
    background-color: rgba(13, 202, 240, 0.05);
}

.stats-card.border-danger {
    border-color: #dc3545 !important;
    background-color: rgba(220, 53, 69, 0.05);
}

.stats-card.border-warning {
    border-color: #ffc107 !important;
    background-color: rgba(255, 193, 7, 0.05);
}

.stats-icon {
    font-size: 2rem;
    opacity: 0.7;
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endsection