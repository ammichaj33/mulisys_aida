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
                            <?php $__currentLoopData = $interestCalculation['schedule']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
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
                                ?>
                            <tr class="<?php echo e($isCancelled ? 'table-danger' : ''); ?>">
                                <td><?php echo e($index + 1); ?></td>
                                <td><?php echo e(date('d M-y', strtotime($payment['date']))); ?></td>
                                <td class="text-end"><?php echo e(number_format($payment['capital_restant'], 2, ',', ' ')); ?></td>
                                <td class="text-end"><?php echo e(number_format($payment['remboursement_fixe'], 2, ',', ' ')); ?></td>
                                <td class="text-end">
                                    <?php if($isCancelled): ?>
                                        <del class="text-danger"><?php echo e(number_format($payment['interet'], 2, ',', ' ')); ?></del>
                                        <br><small class="text-success fw-bold">ANNULÉ</small>
                                    <?php else: ?>
                                        <?php echo e(number_format($payment['interet'], 2, ',', ' ')); ?>

                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($isCancelled): ?>
                                        <del class="text-muted"><?php echo e(number_format($payment['montant_total'], 2, ',', ' ')); ?></del>
                                        <br><small class="text-success fw-bold"><?php echo e(number_format($payment['remboursement_fixe'], 2, ',', ' ')); ?></small>
                                    <?php else: ?>
                                        <strong><?php echo e(number_format($payment['montant_total'], 2, ',', ' ')); ?></strong>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2"><strong>TOTAL ORIGINAL</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end"><?php echo e(number_format($loan->requestAmount, 2, ',', ' ')); ?></th>
                                <th class="text-end"><?php echo e(number_format($interestCalculation['total_interest'], 2, ',', ' ')); ?></th>
                                <th class="text-end"><strong><?php echo e(number_format($interestCalculation['total_amount'], 2, ',', ' ')); ?></strong></th>
                            </tr>
                            <?php if($totalCancelledInterest > 0): ?>
                            <tr class="table-success">
                                <th colspan="2"><strong>ÉCONOMIES (Remboursement anticipé)</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end text-success"><strong>-<?php echo e(number_format($totalCancelledInterest, 2, ',', ' ')); ?></strong></th>
                                <th class="text-end text-success"><strong>-<?php echo e(number_format($totalCancelledInterest, 2, ',', ' ')); ?></strong></th>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <th colspan="2"><strong>MONTANT FINAL DÛ</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end"><?php echo e(number_format($loan->requestAmount, 2, ',', ' ')); ?></th>
                                <th class="text-end"><?php echo e(number_format($interestCalculation['total_interest'] - $totalCancelledInterest, 2, ',', ' ')); ?></th>
                                <th class="text-end"><strong><?php echo e(number_format($totalAmountDue, 2, ',', ' ')); ?></strong></th>
                            </tr>
                            <tr class="table-info">
                                <th colspan="2"><strong>MONTANT REMBOURSÉ</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end"><strong><?php echo e(number_format($totalRepaid, 2, ',', ' ')); ?></strong></th>
                            </tr>
                            <tr class="table-<?php echo e($remainingAmount <= 0 ? 'success' : 'warning'); ?>">
                                <th colspan="2"><strong>RESTE DÛ</strong></th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end">-</th>
                                <th class="text-end"><strong><?php echo e(number_format($remainingAmount, 2, ',', ' ')); ?></strong></th>
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

<?php /**PATH C:\laragon\www\mulisys_aida\resources\views/loans/_repayment-schedule.blade.php ENDPATH**/ ?>