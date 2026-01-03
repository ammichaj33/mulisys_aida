@extends('layouts.app')

@section('title', 'Tableau de bord - Directeur')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</h2>
        </div>
    </div>
</div>


<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Rôle :</strong> Directeur - Vue d'ensemble de l'activité de l'institution de microcrédit.
        </div>
    </div>
</div>

<!-- Statistiques générales -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $totalLoans }}</h3>
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
                    <h3 class="mb-0">{{ number_format($totalAmount, 0, ',', ' ') }} USD</h3>
                    <p class="mb-0">Montant total</p>
                </div>
                <i class="fas fa-dollar-sign stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ number_format($totalRepaid, 0, ',', ' ') }} USD</h3>
                    <p class="mb-0">Remboursé</p>
                </div>
                <i class="fas fa-check-circle stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ number_format($outstandingAmount, 0, ',', ' ') }} USD</h3>
                    <p class="mb-0">En cours</p>
                </div>
                <i class="fas fa-clock stats-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques mensuelles -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Ce mois</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary">{{ $monthlyStats['this_month']['loans'] }}</h4>
                        <small class="text-muted">Crédits</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success">{{ number_format($monthlyStats['this_month']['amount'], 0, ',', ' ') }}</h4>
                        <small class="text-muted">Montant (USD)</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">{{ number_format($monthlyStats['this_month']['repayments'], 0, ',', ' ') }}</h4>
                        <small class="text-muted">Remboursements</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Mois dernier</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary">{{ $monthlyStats['last_month']['loans'] }}</h4>
                        <small class="text-muted">Crédits</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success">{{ number_format($monthlyStats['last_month']['amount'], 0, ',', ' ') }}</h4>
                        <small class="text-muted">Montant (USD)</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">{{ number_format($monthlyStats['last_month']['repayments'], 0, ',', ' ') }}</h4>
                        <small class="text-muted">Remboursements</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Membres avec le plus de crédits -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Top membres</h5>
            </div>
            <div class="card-body">
                @if($topMembers->isEmpty())
                    <div class="text-center py-3">
                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Aucun membre</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Membre</th>
                                    <th>Crédits</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topMembers as $member)
                                    <tr>
                                        <td>
                                            <strong>{{ $member->firstName }} {{ $member->lastName }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $member->phoneNumber }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $member->loan_docs_count }}</span>
                                        </td>
                                        <td>{{ number_format($member->loan_docs_sum_request_amount, 0, ',', ' ') }} USD</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Crédits en retard -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Crédits en retard
                    @if($overdueLoans->count() > 0)
                        <span class="badge bg-danger ms-2">{{ $overdueLoans->count() }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($overdueLoans->isEmpty())
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-muted">Aucun crédit en retard</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Membre</th>
                                    <th>Montant</th>
                                    <th>Retard</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueLoans as $loan)
                                    <tr>
                                        <td><strong>{{ $loan->refNumber }}</strong></td>
                                        <td>{{ $loan->member->firstName }} {{ $loan->member->lastName }}</td>
                                        <td>{{ number_format($loan->requestAmount, 0, ',', ' ') }} USD</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ now()->diffInMonths($loan->endedDate) }} mois
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Évolution des crédits -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution des crédits (6 derniers mois)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th>Nombre de crédits</th>
                                <th>Montant total</th>
                                <th>Moyenne par crédit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loanEvolution as $month)
                                <tr>
                                    <td><strong>{{ $month['month'] }}</strong></td>
                                    <td>{{ $month['loans'] }}</td>
                                    <td>{{ number_format($month['amount'], 0, ',', ' ') }} USD</td>
                                    <td>
                                        @if($month['loans'] > 0)
                                            {{ number_format($month['amount'] / $month['loans'], 0, ',', ' ') }} USD
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

