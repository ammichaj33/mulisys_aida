@extends('layouts.app')

@section('title', 'Gestion des crédits')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt me-2"></i>Gestion des crédits</h2>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('loans.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            @foreach (['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser', 'finalized' => 'Finalisé'] as $key => $label)
                                <option value="{{ $key }}" {{ (request('status') == $key) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Référence, nom, téléphone...">
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Liste des crédits
                    @if ($loans->count() > 0)
                        <span class="badge bg-primary ms-2">{{ $loans->count() }} résultat(s)</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($loans->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun crédit trouvé</p>
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
                                                    case 'draft': $statusClass = 'bg-secondary'; break;
                                                    case 'accepted': $statusClass = 'bg-success'; break;
                                                    case 'rejected': $statusClass = 'bg-danger'; break;
                                                    case 'validated': $statusClass = 'bg-primary'; break;
                                                    case 'done': $statusClass = 'bg-info'; break;
                                                    case 'toreviewed': $statusClass = 'bg-warning'; break;
                                                }
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ ['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser', 'finalized' => 'Finalisé'][$loan->status] }}
                                            </span>
                                        </td>
                                        <td>{{ $loan->submitDate->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('loans.show', $loan->loanDocId) }}" 
                                               class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('loans.schedule', $loan->loanDocId) }}" 
                                               class="btn btn-sm btn-outline-info" title="Échéancier">
                                                <i class="fas fa-calendar"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $loans->withQueryString()->links() }}
                    </div>
                    <div class="text-center text-muted small mt-2">
                        Affichage {{ $loans->firstItem() }} à {{ $loans->lastItem() }} sur {{ $loans->total() }} résultat(s)
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

