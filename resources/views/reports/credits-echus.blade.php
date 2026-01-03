@extends('layouts.app')

@section('title', 'Rapport des crédits échus par périodes')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-file-alt me-2"></i>Rapport des crédits échus par périodes</h2>
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
                    <form method="GET" action="{{ route('reports.credits-echus') }}" class="row g-3">
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
                <a href="{{ route('reports.credits-echus', array_merge(request()->all(), ['pdf' => 1])) }}" 
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
                                    <th>N° Doss</th>
                                    <th>Date</th>
                                    <th>Bénéficiaire</th>
                                    <th>Montant</th>
                                    <th>Intérêt</th>
                                    <th>Total remboursé</th>
                                    <th>Reste à rembourser</th>
                                    <th>Pénalité</th>
                                    <th>Échéance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                <tr>
                                    <td>{{ $loan->refNumber }}</td>
                                    <td>{{ $loan->submitDate->format('d/m/Y') }}</td>
                                    <td>{{ $loan->member->firstName }} {{ $loan->member->lastName }}</td>
                                    <td class="text-end">{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</td>
                                    <td class="text-end">{{ number_format($loan->totalAmountDue - $loan->requestAmount, 2, ',', ' ') }} USD</td>
                                    <td class="text-end text-success">{{ number_format($loan->totalRepaid, 2, ',', ' ') }} USD</td>
                                    <td class="text-end text-danger">{{ number_format($loan->remainingAmount, 2, ',', ' ') }} USD</td>
                                    <td class="text-end">{{ number_format($loan->totalPenalty, 2, ',', ' ') }} USD</td>
                                    <td>{{ $loan->dueDate->format('d-m-Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Aucun crédit échu trouvé pour cette période</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">TOTAL</th>
                                    <th class="text-end">{{ number_format($totalAmount, 2, ',', ' ') }} USD</th>
                                    <th class="text-end">{{ number_format($totalInterest, 2, ',', ' ') }} USD</th>
                                    <th class="text-end">{{ number_format($totalRepaid, 2, ',', ' ') }} USD</th>
                                    <th class="text-end">{{ number_format($totalRemaining, 2, ',', ' ') }} USD</th>
                                    <th class="text-end">{{ number_format($totalPenalty, 2, ',', ' ') }} USD</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

