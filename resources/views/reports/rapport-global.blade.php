@extends('layouts.app')

@section('title', 'Rapport Global des crédits par période')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-file-alt me-2"></i>Rapport Global des crédits par période</h2>
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
                    <form method="GET" action="{{ route('reports.rapport-global') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="startDate" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" 
                                   value="{{ $startDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="endDate" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" 
                                   value="{{ $endDate }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton Imprimer PDF -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                <a href="{{ route('reports.rapport-global', array_merge(request()->all(), ['pdf' => 1])) }}" 
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf me-2"></i>Imprimer PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Tableau des résultats -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Résultats du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Catégorie</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Total crédit</strong></td>
                                    <td class="text-end"><strong>{{ number_format($totalCredit, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Total intérêt</strong></td>
                                    <td class="text-end"><strong>{{ number_format($totalInterest, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Total remboursé</strong></td>
                                    <td class="text-end text-success"><strong>{{ number_format($totalRepaid, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Reste à rembourser</strong></td>
                                    <td class="text-end text-danger"><strong>{{ number_format($totalRemaining, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Total pénalité</strong></td>
                                    <td class="text-end"><strong>{{ number_format($totalPenalty, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

