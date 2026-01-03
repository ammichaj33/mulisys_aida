@extends('layouts.app')

@section('title', 'Rapport financier')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-bar me-2"></i>Rapport financier</h2>
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
                <form method="GET" action="{{ route('reports.financial') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="date_from" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ $dateFrom->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="date_to" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ $dateTo->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="{{ route('reports.financial') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques générales -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $stats['total_loans'] }}</h3>
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
                    <h3 class="mb-0">{{ number_format($stats['total_loan_amount'], 0, ',', ' ') }} USD</h3>
                    <p class="mb-0">Montant des crédits</p>
                </div>
                <i class="fas fa-dollar-sign stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ number_format($stats['total_repayments'], 0, ',', ' ') }} USD</h3>
                    <p class="mb-0">Remboursements</p>
                </div>
                <i class="fas fa-check-circle stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ number_format($stats['total_penalties'], 0, ',', ' ') }} USD</h3>
                    <p class="mb-0">Pénalités</p>
                </div>
                <i class="fas fa-exclamation-triangle stats-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques des membres -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $stats['active_members'] }}</h3>
                    <p class="mb-0">Membres actifs</p>
                </div>
                <i class="fas fa-users stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $stats['overdue_loans'] }}</h3>
                    <p class="mb-0">Crédits en retard</p>
                </div>
                <i class="fas fa-clock stats-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Résumé financier -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Résumé financier</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Recettes</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-up text-success me-2"></i>Remboursements: {{ number_format($stats['total_repayments'], 2, ',', ' ') }} USD</li>
                            <li><i class="fas fa-arrow-up text-success me-2"></i>Pénalités: {{ number_format($stats['total_penalties'], 2, ',', ' ') }} USD</li>
                            <li class="border-top pt-2 mt-2"><strong>Total recettes: {{ number_format($stats['total_repayments'] + $stats['total_penalties'], 2, ',', ' ') }} USD</strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Dépenses</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-down text-danger me-2"></i>Crédits octroyés: {{ number_format($stats['total_loan_amount'], 2, ',', ' ') }} USD</li>
                            <li class="border-top pt-2 mt-2"><strong>Total dépenses: {{ number_format($stats['total_loan_amount'], 2, ',', ' ') }} USD</strong></li>
                        </ul>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Bilan financier</h6>
                            @php
                                $totalIncome = $stats['total_repayments'] + $stats['total_penalties'];
                                $totalExpenses = $stats['total_loan_amount'];
                                $balance = $totalIncome - $totalExpenses;
                            @endphp
                            <p class="mb-0">
                                <strong>Bilan: {{ number_format($balance, 2, ',', ' ') }} USD</strong>
                                @if($balance > 0)
                                    <span class="text-success">(Positif)</span>
                                @elseif($balance < 0)
                                    <span class="text-danger">(Négatif)</span>
                                @else
                                    <span class="text-muted">(Équilibré)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


