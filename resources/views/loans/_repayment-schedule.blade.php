<!-- Calendrier de remboursement détaillé -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Calendrier de remboursement</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>N°</th>
                                <th>Mois</th>
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
                                    
                                    foreach ($cancelledInterests as $cancelled) {
                                        if ($cancelled->cancelledMonth == $paymentMonth) {
                                            $isCancelled = true;
                                            $cancelledAmount = $cancelled->cancelledInterestAmount;
                                            break;
                                        }
                                    }
                                @endphp
                            <tr class="{{ $isCancelled ? 'table-danger' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ date('d M-y', strtotime($payment['date'])) }}</td>
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
                        <tfoot class="table-light">
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

