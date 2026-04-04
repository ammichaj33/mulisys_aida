<!-- Historique des remboursements pour autres rôles -->
<div class="row mt-4" id="repayments">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des remboursements</h5>
                <span class="badge bg-primary"><?php echo e($loan->loanRepayments->count()); ?> remboursement(s)</span>
            </div>
            <div class="card-body">
                <?php if($loan->loanRepayments->isEmpty()): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun remboursement enregistré</p>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-repayments')): ?>
                            <?php if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere')): ?>
                                <a href="<?php echo e(route('caissiere.repayments.create', ['loan_id' => $loan->loanDocId])); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Enregistrer le premier remboursement
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Type</th>
                                    <th>Enregistré par</th>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-repayments')): ?>
                                        <?php if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere')): ?>
                                            <th>Date d'enregistrement</th>
                                            <th>Actions</th>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $loan->loanRepayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $repayment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($repayment->repaymentDate->format('d/m/Y')); ?></strong>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold"><?php echo e($calculationService->formatMoney($repayment->amount)); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo e($repayment->repaymentType->repaymentName ?? 'N/A'); ?></span>
                                        </td>
                                        <td>
                                            <small><?php echo e($repayment->user->fullName ?? 'Système'); ?></small>
                                        </td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-repayments')): ?>
                                            <?php if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere')): ?>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo e($repayment->createdAt->format('d/m/Y H:i')); ?>

                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="<?php echo e(route('receipts.repayment', $repayment->loanRepaymentId)); ?>" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-success" 
                                                       title="Imprimer reçu">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </td>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="<?php echo e(($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere')) ? '5' : '4'); ?>" class="text-end">Total remboursé:</th>
                                    <th class="text-success"><?php echo e($calculationService->formatMoney($totalRepaid)); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php /**PATH C:\laragon\www\mulisys_aida\resources\views/loans/_repayment-history.blade.php ENDPATH**/ ?>