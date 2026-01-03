@extends('layouts.app')

@section('title', 'Demandes de crédit - Réceptionniste')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-list me-2"></i>Demandes de crédit</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('receptionniste.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
                <a href="{{ route('receptionniste.loans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvelle demande
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
            <strong>Rôle :</strong> Réceptionniste - Vous pouvez consulter, modifier et gérer toutes les demandes de crédit.
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <a href="{{ route('receptionniste.loans.index', ['status' => 'draft']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'draft') border-primary @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['draft'] }}</h3>
                        <p class="mb-0">Brouillons</p>
                    </div>
                    <i class="fas fa-edit stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('receptionniste.loans.index', ['status' => 'accepted']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'accepted') border-warning @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['accepted'] }}</h3>
                        <p class="mb-0">Acceptées</p>
                    </div>
                    <i class="fas fa-check-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('receptionniste.loans.index', ['status' => 'validated']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'validated') border-success @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['validated'] }}</h3>
                        <p class="mb-0">Validées</p>
                    </div>
                    <i class="fas fa-check-double stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('receptionniste.loans.index', ['status' => 'rejected']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'rejected') border-danger @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                        <p class="mb-0">Rejetées</p>
                    </div>
                    <i class="fas fa-times-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('receptionniste.loans.index', ['status' => 'done']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'done') border-info @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['done'] }}</h3>
                        <p class="mb-0">Terminées</p>
                    </div>
                    <i class="fas fa-flag-checkered stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('receptionniste.loans.index') }}" class="text-decoration-none">
            <div class="card stats-card @if(!request('status')) border-secondary @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $loans->total() }}</h3>
                        <p class="mb-0">Total</p>
                    </div>
                    <i class="fas fa-list stats-icon"></i>
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
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Référence, nom, prénom, téléphone...">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="draft" {{ (request('status') == 'draft') ? 'selected' : '' }}>Brouillon</option>
                            <option value="accepted" {{ (request('status') == 'accepted') ? 'selected' : '' }}>Accepté</option>
                            <option value="validated" {{ (request('status') == 'validated') ? 'selected' : '' }}>Validé</option>
                            <option value="rejected" {{ (request('status') == 'rejected') ? 'selected' : '' }}>Rejeté</option>
                            <option value="done" {{ (request('status') == 'done') ? 'selected' : '' }}>Terminé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="{{ route('receptionniste.loans.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Liste des demandes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Liste des demandes
                    @if ($loans->count() > 0)
                        <span class="badge bg-primary ms-2">{{ $loans->count() }} résultat(s)</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if ($loans->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune demande de crédit trouvée</p>
                        <a href="{{ route('receptionniste.loans.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Créer une nouvelle demande
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
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
                                @foreach ($loans as $loan)
                                    <tr>
                                        <td>
                                            <strong>{{ $loan->refNumber }}</strong>
                                        </td>
                                        <td>
                                            {{ $loan->member->firstName }} {{ $loan->member->lastName }}
                                            <br>
                                            <small class="text-muted">{{ $loan->member->phoneNumber }}</small>
                                        </td>
                                        <td>{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</td>
                                        <td>{{ $loan->loanMonths }} mois</td>
                                        <td>
                                            @php
                                                $statusLabels = [
                                                    'draft' => 'Brouillon',
                                                    'accepted' => 'Accepté',
                                                    'validated' => 'Validé',
                                                    'rejected' => 'Rejeté',
                                                    'done' => 'Terminé'
                                                ];
                                                $statusClass = [
                                                    'draft' => 'bg-secondary',
                                                    'accepted' => 'bg-warning',
                                                    'validated' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'done' => 'bg-info'
                                                ];
                                                $statusLabel = $statusLabels[$loan->status] ?? ucfirst($loan->status);
                                                $statusClassValue = $statusClass[$loan->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClassValue }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td>{{ date('d/m/Y', strtotime($loan->createdAt)) }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('receptionniste.loans.show', $loan->loanDocId) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($loan->status == 'draft')
                                                    <a href="{{ route('receptionniste.loans.edit', $loan->loanDocId) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
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
    </div>
</div>
@endsection
