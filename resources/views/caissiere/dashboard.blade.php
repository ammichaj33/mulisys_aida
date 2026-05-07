@extends('layouts.app')

@section('title', 'Tableau de bord - Caissière')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-cash-register me-2"></i>Tableau de bord - Caissière</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('members.index') }}" class="btn btn-outline-info">
                    <i class="fas fa-users me-2"></i>Liste membres
                </a>
                <a href="{{ route('penalties.index') }}" class="btn btn-outline-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Pénalités
                </a>
                <a href="{{ route('caissiere.repayments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau remboursement
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
            <strong>Rôle :</strong> Caissière - Vous êtes responsable de l'enregistrement des remboursements et de la gestion des pénalités.
        </div>
    </div>
</div>


<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <a href="{{ route('caissiere.dashboard', ['status' => 'draft']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'draft') border-secondary @endif">
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
        <a href="{{ route('caissiere.dashboard', ['status' => 'accepted']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'accepted') border-warning @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['accepted'] }}</h3>
                        <p class="mb-0">À valider</p>
                    </div>
                    <i class="fas fa-clock stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('caissiere.dashboard', ['status' => 'validated']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'validated') border-primary @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['validated'] }}</h3>
                        <p class="mb-0">Validés</p>
                    </div>
                    <i class="fas fa-check-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('caissiere.dashboard', ['status' => 'rejected']) }}" class="text-decoration-none">
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
    <div class="col-md-2 mb-3">
        <a href="{{ route('caissiere.dashboard', ['status' => 'done']) }}" class="text-decoration-none">
            <div class="card stats-card @if(request('status') == 'done') border-success @endif">
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
    <div class="col-md-2 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $todayRepayments['count'] ?? 0 }}</h3>
                    <p class="mb-0">Remboursements aujourd'hui</p>
                </div>
                <i class="fas fa-calendar-day stats-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques financières -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-success">
                    <i class="fas fa-money-bill-wave me-2"></i>Remboursements aujourd'hui
                </h5>
                <h3 class="text-success">{{ round($todayRepayments['total'] ?? 0, 2) }} USD</h3>
                <small class="text-muted">{{ $todayRepayments['count'] ?? 0 }} transaction(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-info">
                    <i class="fas fa-calendar-week me-2"></i>Cette semaine
                </h5>
                <h3 class="text-info">{{ round($weekRepayments['total'] ?? 0, 2) }} USD</h3>
                <small class="text-muted">{{ $weekRepayments['count'] ?? 0 }} transaction(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-primary">
                    <i class="fas fa-calendar-alt me-2"></i>Ce mois
                </h5>
                <h3 class="text-primary">{{ round($monthRepayments['total'] ?? 0, 2) }} USD</h3>
                <small class="text-muted">{{ $monthRepayments['count'] ?? 0 }} transaction(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Pénalités en attente
                </h5>
                <h3 class="text-warning">{{ round($pendingPenalties['total'] ?? 0, 2) }} USD</h3>
                <small class="text-muted">{{ $pendingPenalties['count'] ?? 0 }} pénalité(s)</small>
            </div>
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
                <form method="GET" action="{{ route('caissiere.dashboard') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            @foreach (['draft' => 'Brouillon', 'accepted' => 'À valider', 'validated' => 'Validé', 'rejected' => 'Rejeté', 'done' => 'Terminé'] as $key => $label)
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

                    <div class="w-100"></div>

                    <div class="col-md-2">
                        <label for="overdue" class="form-label">Insolvables</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="overdue" name="overdue" {{ request('overdue') ? 'checked' : '' }}>
                            <label class="form-check-label" for="overdue">
                                Date prévue dépassée
                            </label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="overdue_from" class="form-label">Du</label>
                        <input type="date" class="form-control" id="overdue_from" name="overdue_from"
                               value="{{ request('overdue_from') }}"
                               @if(!request('overdue')) disabled @endif>
                    </div>
                    <div class="col-md-2">
                        <label for="overdue_to" class="form-label">Au</label>
                        <input type="date" class="form-control" id="overdue_to" name="overdue_to"
                               value="{{ request('overdue_to') }}"
                               @if(!request('overdue')) disabled @endif>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            @if(request('overdue'))
                                <a href="{{ route('caissiere.dashboard.insolvables.pdf', request()->query()) }}" target="_blank" class="btn btn-outline-dark">
                                    <i class="fas fa-print me-2"></i>Imprimer
                                </a>
                            @endif
                            <a href="{{ route('caissiere.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const overdue = document.getElementById('overdue');
    const from = document.getElementById('overdue_from');
    const to = document.getElementById('overdue_to');
    if (!overdue || !from || !to) return;
    const sync = () => {
        const enabled = overdue.checked;
        from.disabled = !enabled;
        to.disabled = !enabled;
        if (!enabled) {
            // garder les valeurs mais ne pas les envoyer si l'utilisateur n'a pas coché "Insolvables"
            from.value = '';
            to.value = '';
        }
    };
    overdue.addEventListener('change', sync);
    sync();
});
</script>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Tous les dossiers
                    @if ($recentRequests->count() > 0)
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
                        <p class="text-muted">Aucun crédit à gérer</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Membre</th>
                                    <th>Montant</th>
                                    <th>Intérêts</th>
                                    <th>Statut</th>
                                    <th>Remboursé</th>
                                    <th>Reste dû</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentRequests as $request)
                                    <tr>
                                        <td>
                                            <strong>{{ htmlspecialchars($request->refNumber) }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($request->member->photo)
                                                    <img src="{{ route('members.photo', $request->member->memberId) }}" 
                                                         alt="Photo de {{ $request->member->firstName }} {{ $request->member->lastName }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 35px; height: 35px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ htmlspecialchars($request->member->firstName . ' ' . $request->member->lastName) }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ htmlspecialchars($request->member->phoneNumber) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ round($request->requestAmount, 2) }} USD</td>
                                        <td>{{ round($request->interestAmount ?? 0, 2) }} USD</td>
                                        <td>
                                            @php
                                                $statusLabels = [
                                                    'draft' => 'Brouillon',
                                                    'accepted' => 'À valider',
                                                    'validated' => 'Validé',
                                                    'rejected' => 'Rejeté',
                                                    'done' => 'Terminé'
                                                ];
                                                $statusClass = [
                                                    'draft' => 'bg-secondary',
                                                    'accepted' => 'bg-warning',
                                                    'validated' => 'bg-primary',
                                                    'rejected' => 'bg-danger',
                                                    'done' => 'bg-success'
                                                ];
                                                $statusLabel = $statusLabels[$request->status] ?? ucfirst($request->status);
                                                $statusClassValue = $statusClass[$request->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClassValue }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success">{{ round($request->loan_repayments_sum_amount ?? 0, 2) }} USD</span>
                                        </td>
                                        <td>
                                            <span class="text-{{ ($request->remainingAmount ?? 0) > 0 ? 'danger' : 'success' }}">
                                                {{ round($request->remainingAmount ?? 0, 2) }} USD
                                            </span>
                                        </td>
                                        <td>{{ date('d/m/Y', strtotime($request->createdAt)) }}</td>
                                        <td>
                                            <a href="{{ route('caissiere.loans.show', $request->loanDocId) }}" 
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
                    <div class="d-flex justify-content-center mt-3">
                        @if(request('overdue'))
                            {{ $recentRequests->links() }}
                        @else
                            {{ $recentRequests->appends(request()->query())->links() }}
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
