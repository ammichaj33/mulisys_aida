@extends('layouts.app')

@section('title', 'Bilan crédit')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-file-alt me-2"></i>Bilan crédit</h2>
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
                    <form method="GET" action="{{ route('reports.bilan-par-periode') }}" class="row g-3">
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
                <a href="{{ route('reports.bilan-par-periode', array_merge(request()->all(), ['pdf' => 1])) }}" 
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
                    <h5 class="mb-0">Bilan du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h5>
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
                                    <td><strong>Capital injecté</strong></td>
                                    <td class="text-end"><strong>{{ number_format($capitalInjecte, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Intérêt attendu</strong></td>
                                    <td class="text-end"><strong>{{ number_format($interetAttendu, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Intérêt collecté</strong></td>
                                    <td class="text-end text-success"><strong>{{ number_format($interetCollecte, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Reste des intérêts à collecter</strong></td>
                                    <td class="text-end text-danger"><strong>{{ number_format($resteInteretACollecter, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Total pénalité collecter</strong></td>
                                    <td class="text-end"><strong>{{ number_format($totalPenaliteCollecter, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Pénalité collecter</strong></td>
                                    <td class="text-end text-success"><strong>{{ number_format($penaliteCollecter, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Reste pénalité à collecter</strong></td>
                                    <td class="text-end text-danger"><strong>{{ number_format($restePenaliteACollecter, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Frais d'étude collectés</strong></td>
                                    <td class="text-end"><strong>{{ number_format($fraisEtudeCollectes, 2, ',', ' ') }} USD</strong></td>
                                </tr>
                                <tr style="border-top: 2px solid #000;">
                                    <td><strong>Situation mutuelle</strong></td>
                                    <td class="text-end"><strong>{{ number_format($situationMutuelle, 2, ',', ' ') }} USD</strong></td>
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

