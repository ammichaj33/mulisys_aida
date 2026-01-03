@extends('layouts.app')

@section('title', 'Cashflow par catégorie')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-chart-pie me-2"></i>Cashflow par catégorie</h2>
                <a href="{{ route('cashflow.reports.by-category', array_merge(request()->all(), ['pdf' => 1])) }}" 
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
                    <form method="GET" action="{{ route('cashflow.reports.by-category') }}" class="row g-3">
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

    <!-- Totaux -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-arrow-down stats-icon"></i>
                    <h3>{{ number_format($totalIncome, 2, ',', ' ') }} USD</h3>
                    <p>Total Entrées</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-arrow-up stats-icon"></i>
                    <h3>{{ number_format($totalExpense, 2, ',', ' ') }} USD</h3>
                    <p>Total Sorties</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                <div class="card-body text-center text-white">
                    <i class="fas fa-balance-scale stats-icon"></i>
                    <h3>{{ number_format($balance, 2, ',', ' ') }} USD</h3>
                    <p>Solde</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails par catégorie -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Détails par catégorie</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Type</th>
                                    <th>Entrées</th>
                                    <th>Sorties</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->categoryName }}</td>
                                    <td>
                                        @if($category->categoryType == 'income')
                                            <span class="badge bg-success">Entrée</span>
                                        @else
                                            <span class="badge bg-danger">Sortie</span>
                                        @endif
                                    </td>
                                    <td class="text-success">
                                        @if($category->totalIncome > 0)
                                            <strong>+{{ number_format($category->totalIncome, 2, ',', ' ') }} USD</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-danger">
                                        @if($category->totalExpense > 0)
                                            <strong>-{{ number_format($category->totalExpense, 2, ',', ' ') }} USD</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="{{ $category->total >= 0 ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ number_format($category->total, 2, ',', ' ') }} USD</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Aucune catégorie avec transactions pour cette période</td>
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

