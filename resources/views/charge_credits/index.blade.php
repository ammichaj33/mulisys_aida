@extends('layouts.app')

@section('title', 'Liste des demandes de crédit')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-list me-2"></i>Liste des demandes de crédit</h2>
            <div>
                <a href="{{ route('charge_credits.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['total'] }}</h4>
                <small>Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['draft'] }}</h4>
                <small>Brouillons</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['accepted'] }}</h4>
                <small>Acceptés</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['rejected'] }}</h4>
                <small>Rejetés</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['validated'] }}</h4>
                <small>Validés</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ $stats['done'] }}</h4>
                <small>Terminés</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('charge_credits.loans.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Référence, nom, téléphone...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepté</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                        <option value="validated" {{ request('status') == 'validated' ? 'selected' : '' }}>Validé</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Terminé</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('charge_credits.loans.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Effacer
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des demandes -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Demandes de crédit</h5>
    </div>
    <div class="card-body">
        @if($loans->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Référence</th>
                            <th>Membre</th>
                            <th>Montant</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loans as $loan)
                            <tr>
                                <td>
                                    <strong>{{ $loan->refNumber }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($loan->member->photo)
                                            <img src="{{ route('members.photo', $loan->member->memberId) }}" 
                                                 alt="Photo de {{ $loan->member->firstName }} {{ $loan->member->lastName }}" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 35px; height: 35px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 35px; height: 35px;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $loan->member->firstName }} {{ $loan->member->lastName }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $loan->member->phoneNumber }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</td>
                                <td>{{ $loan->loanMonths }} mois</td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'draft' => 'bg-secondary',
                                            'accepted' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'validated' => 'bg-primary',
                                            'done' => 'bg-info',
                                            'toreviewed' => 'bg-warning',
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Brouillon',
                                            'accepted' => 'Accepté',
                                            'rejected' => 'Rejeté',
                                            'validated' => 'Validé',
                                            'done' => 'Terminé',
                                            'toreviewed' => 'À réviser',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusClasses[$loan->status] ?? 'bg-secondary' }}">
                                        {{ $statusLabels[$loan->status] ?? $loan->status }}
                                    </span>
                                </td>
                                <td>{{ $loan->createdAt->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('charge_credits.loans.show', $loan->loanDocId) }}" 
                                       class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($loan->status == 'draft')
                                        <a href="{{ route('charge_credits.loans.validate-form', $loan->loanDocId) }}" 
                                           class="btn btn-sm btn-outline-success me-1" title="Valider">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $loans->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune demande de crédit trouvée</h5>
                <p class="text-muted">Aucune demande ne correspond aux critères de recherche.</p>
            </div>
        @endif
    </div>
</div>
@endsection


