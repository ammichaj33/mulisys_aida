@extends('layouts.app')

@section('title', 'Rapport des crédits')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-bar me-2"></i>Rapport des crédits</h2>
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
                <form method="GET" action="{{ route('reports.loans') }}" class="row g-3">
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
                        <label for="date_from" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="{{ route('reports.loans') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <p class="mb-0">Total crédits</p>
                </div>
                <i class="fas fa-file-alt stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ number_format($stats['total_amount'], 0, ',', ' ') }} USD</h3>
                    <p class="mb-0">Montant total</p>
                </div>
                <i class="fas fa-dollar-sign stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition par statut</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($stats['by_status'] as $status)
                        <div class="col-6 mb-2">
                            <div class="d-flex justify-content-between">
                                <span>{{ ['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser', 'finalized' => 'Finalisé'][$status->status] ?? $status->status }}</span>
                                <span class="badge bg-primary">{{ $status->count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
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
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $loans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

