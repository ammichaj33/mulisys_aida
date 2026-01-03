@extends('layouts.app')

@section('title', 'Rapport de trésorerie')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-chart-bar me-2"></i>Rapport de trésorerie</h2>
                <a href="{{ route('cashflow.reports.treasury', array_merge(request()->all(), ['pdf' => 1])) }}" 
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf me-2"></i>Imprimer PDF
                </a>
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
                    <form method="GET" action="{{ route('cashflow.reports.treasury') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="startDate" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" 
                                   value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label for="endDate" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" 
                                   value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-wallet stats-icon"></i>
                    <h3>{{ number_format($openingBalance, 2, ',', ' ') }} USD</h3>
                    <p>Solde d'ouverture</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-arrow-down stats-icon"></i>
                    <h3>{{ number_format($periodIncome, 2, ',', ' ') }} USD</h3>
                    <p>Entrées période</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-arrow-up stats-icon"></i>
                    <h3>{{ number_format($periodExpense, 2, ',', ' ') }} USD</h3>
                    <p>Sorties période</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-balance-scale stats-icon"></i>
                    <h3>{{ number_format($closingBalance, 2, ',', ' ') }} USD</h3>
                    <p>Solde de clôture</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Détail par compte -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Détail par compte</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Compte</th>
                                    <th>Type</th>
                                    <th>Solde d'ouverture</th>
                                    <th>Entrées</th>
                                    <th>Sorties</th>
                                    <th>Solde de clôture</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $account)
                                <tr>
                                    <td>{{ $account->accountName }}</td>
                                    <td>
                                        @if($account->accountType == 'cash')
                                            <span class="badge bg-info">Espèces</span>
                                        @elseif($account->accountType == 'bank')
                                            <span class="badge bg-primary">Banque</span>
                                        @else
                                            <span class="badge bg-success">Mobile Money</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($account->openingBalance, 2, ',', ' ') }} USD</td>
                                    <td class="text-success">
                                        @if($account->periodIncome > 0)
                                            <strong>+{{ number_format($account->periodIncome, 2, ',', ' ') }} USD</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-danger">
                                        @if($account->periodExpense > 0)
                                            <strong>-{{ number_format($account->periodExpense, 2, ',', ' ') }} USD</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="{{ $account->closingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ number_format($account->closingBalance, 2, ',', ' ') }} USD</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Aucun compte</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

