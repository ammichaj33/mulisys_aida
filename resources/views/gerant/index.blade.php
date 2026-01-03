@extends('layouts.app')

@section('title', 'Liste des prêts')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-list me-2"></i>Liste des prêts</h2>
            <a href="{{ route('gerant.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
            </a>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">{{ $stats['total'] }}</h5>
                <p class="card-text small">Total</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">{{ $stats['draft'] }}</h5>
                <p class="card-text small">Brouillon</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info">{{ $stats['accepted'] }}</h5>
                <p class="card-text small">Accepté</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-danger">{{ $stats['rejected'] }}</h5>
                <p class="card-text small">Rejeté</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">{{ $stats['validated'] }}</h5>
                <p class="card-text small">Validé</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-secondary">{{ $stats['done'] }}</h5>
                <p class="card-text small">Terminé</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('gerant.loans.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Référence, nom, téléphone...">
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
                    <a href="{{ route('gerant.loans.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Effacer
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des prêts -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Prêts ({{ $loans->total() }})</h5>
    </div>
    <div class="card-body">
        @if($loans->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun prêt trouvé</h5>
                <p class="text-muted">Aucun prêt ne correspond aux critères de recherche.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
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
                                <td>
                                    @php
                                        $statusClass = '';
                                        switch ($loan->status) {
                                            case 'draft': $statusClass = 'bg-warning'; break;
                                            case 'accepted': $statusClass = 'bg-info'; break;
                                            case 'rejected': $statusClass = 'bg-danger'; break;
                                            case 'validated': $statusClass = 'bg-success'; break;
                                            case 'done': $statusClass = 'bg-secondary'; break;
                                            case 'toreviewed': $statusClass = 'bg-warning'; break;
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser'][$loan->status] }}
                                    </span>
                                </td>
                                <td>{{ $loan->createdAt->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('gerant.loans.show', $loan->loanDocId) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
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
        @endif
    </div>
</div>
@endsection


