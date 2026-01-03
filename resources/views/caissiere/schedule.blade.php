@extends('layouts.app')

@section('title', 'Échéancier de remboursement')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-calendar-alt me-2"></i>Échéancier de remboursement
    </h1>
    <div>
        <a href="{{ route('caissiere.loans.show', $loan->loanDocId) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour au crédit
        </a>
    </div>
</div>

<!-- Informations du crédit -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations du crédit</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Référence :</strong> {{ $loan->refNumber }}</p>
                        <p><strong>Membre :</strong> {{ $loan->member->firstName }} {{ $loan->member->lastName }}</p>
                        <p><strong>Téléphone :</strong> {{ $loan->member->phoneNumber }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Montant demandé :</strong> {{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</p>
                        <p><strong>Taux d'intérêt :</strong> {{ $loan->interestRate }}%</p>
                        <p><strong>Durée :</strong> {{ $loan->loanMonths }} mois</p>
                        <p><strong>Date de démarrage :</strong> {{ date('d/m/Y', strtotime($loan->submitDate)) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Échéancier -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Échéancier mensuel</h5>
                <div class="btn-group" role="group">
                    <a href="{{ route('loans.schedule.pdf', $loan->loanDocId) }}" 
                       class="btn btn-success">
                        <i class="fas fa-file-pdf me-2"></i>Générer PDF
                    </a>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead style="background-color: #7c5cba; color: white;">
                            <tr>
                                <th class="text-center">N°</th>
                                <th class="text-center">Mois</th>
                                <th class="text-end">Capital restant</th>
                                <th class="text-end">Montant Decre</th>
                                <th class="text-end">Intérêt</th>
                                <th class="text-end">Remb. Decre</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($interestCalculation['schedule'] as $index => $payment)
                                @php
                                    $paymentMonth = date('Y-m', strtotime($payment['date']));
                                    $isCancelled = false;
                                    $cancelledAmount = 0;
                                    
                                    // Vérifier si ce mois a des intérêts annulés
                                    foreach ($cancelledInterests as $cancelled) {
                                        if ($cancelled->cancelledMonth == $paymentMonth) {
                                            $isCancelled = true;
                                            $cancelledAmount = $cancelled->cancelledInterestAmount;
                                            break;
                                        }
                                    }
                                @endphp
                            <tr class="{{ $isCancelled ? 'table-danger' : '' }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ date('d M-y', strtotime($payment['date'])) }}</td>
                                <td class="text-end">{{ number_format($payment['capital_restant'], 2, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($payment['remboursement_fixe'], 2, ',', ' ') }}</td>
                                <td class="text-end">
                                    @if($isCancelled)
                                        <del class="text-danger">{{ number_format($payment['interet'], 2, ',', ' ') }}</del>
                                        <br><small class="text-success fw-bold">ANNULÉ</small>
                                    @else
                                        {{ number_format($payment['interet'], 2, ',', ' ') }}
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($isCancelled)
                                        <del class="text-muted">{{ number_format($payment['montant_total'], 2, ',', ' ') }}</del>
                                        <br><small class="text-success fw-bold">{{ number_format($payment['remboursement_fixe'], 2, ',', ' ') }}</small>
                                    @else
                                        <strong>{{ number_format($payment['montant_total'], 2, ',', ' ') }}</strong>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2"><strong>TOTAL ORIGINAL</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end">{{ number_format($loan->requestAmount, 2, ',', ' ') }}</th>
                                <th class="text-end">{{ number_format($interestCalculation['total_interest'], 2, ',', ' ') }}</th>
                                <th class="text-end"><strong>{{ number_format($interestCalculation['total_amount'], 2, ',', ' ') }}</strong></th>
                            </tr>
                            @if($totalCancelledInterest > 0)
                            <tr class="table-success">
                                <th colspan="2"><strong>ÉCONOMIES (Remboursement anticipé)</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end text-success"><strong>-{{ number_format($totalCancelledInterest, 2, ',', ' ') }}</strong></th>
                                <th class="text-end text-success"><strong>-{{ number_format($totalCancelledInterest, 2, ',', ' ') }}</strong></th>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th colspan="2"><strong>MONTANT FINAL DÛ</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end">{{ number_format($loan->requestAmount, 2, ',', ' ') }}</th>
                                <th class="text-end">{{ number_format($interestCalculation['total_interest'] - $totalCancelledInterest, 2, ',', ' ') }}</th>
                                <th class="text-end"><strong>{{ number_format($totalAmountDue, 2, ',', ' ') }}</strong></th>
                            </tr>
                            <tr class="table-info">
                                <th colspan="2"><strong>MONTANT REMBOURSÉ</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end"><strong>{{ number_format($totalRepaid, 2, ',', ' ') }}</strong></th>
                            </tr>
                            <tr class="table-{{ $remainingAmount <= 0 ? 'success' : 'warning' }}">
                                <th colspan="2"><strong>RESTE DÛ</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end"><strong>{{ number_format($remainingAmount, 2, ',', ' ') }}</strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Informations supplémentaires -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-chart-line me-2"></i>Avantages du calcul dégressif</h6>
                            <ul class="mb-0 small">
                                <li>Intérêts dégressifs sur le capital restant</li>
                                <li>Coût total du crédit plus transparent</li>
                                <li>Équitable pour l'emprunteur</li>
                                <li>Les intérêts diminuent au fil des remboursements</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Note importante</h6>
                            <p class="mb-0 small">
                                Ce calendrier est basé sur un remboursement mensuel régulier. 
                                Les dates peuvent être ajustées selon les modalités du contrat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn, .alert, nav, .sidebar, footer {
            display: none !important;
        }
        .card {
            border: 1px solid #000 !important;
        }
        .table {
            font-size: 12px;
        }
    }
</style>
@endpush
@endsection

