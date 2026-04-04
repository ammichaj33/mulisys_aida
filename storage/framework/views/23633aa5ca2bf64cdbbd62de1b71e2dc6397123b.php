

<?php $__env->startSection('title', 'Remboursement anticipé'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clock me-2"></i>Remboursement anticipé</h2>
            <a href="<?php echo e(route('caissiere.loans.show', $loan->loanDocId)); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Informations du crédit -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations du crédit</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Référence:</strong>
                        <p class="text-primary"><?php echo e($loan->refNumber); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Membre:</strong>
                        <p><?php echo e($loan->member->firstName); ?> <?php echo e($loan->member->lastName); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Montant du crédit:</strong>
                        <p class="text-success h5"><?php echo e($calculationService->formatMoney($loan->requestAmount)); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Durée:</strong>
                        <p><?php echo e($loan->loanMonths); ?> mois</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Taux d'intérêt:</strong>
                        <p><?php echo e($loan->interestRate); ?>% par mois</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Date d'octroi:</strong>
                        <p><?php echo e($loan->submitDate->format('d/m/Y')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculs du remboursement anticipé -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Calcul du remboursement anticipé</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Remboursement anticipé :</strong> 
                    Le membre rembourse le capital restant + les intérêts dus jusqu'à la date de remboursement. 
                    Les intérêts des mois non encore échus seront annulés.
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Capital restant à rembourser</h6>
                                <h4 class="text-primary mb-0"><?php echo e($calculationService->formatMoney($earlyRepaymentDetails['capitalRestant'])); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Intérêts dus jusqu'à la date</h6>
                                <h4 class="text-info mb-0"><?php echo e($calculationService->formatMoney($earlyRepaymentDetails['interetsDus'])); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($earlyRepaymentDetails['interetsACanceler'] > 0): ?>
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>Intérêts qui seront annulés</h6>
                    <p class="mb-2"><strong>Total des intérêts annulés:</strong> 
                        <span class="text-success h5"><?php echo e($calculationService->formatMoney($earlyRepaymentDetails['interetsACanceler'])); ?></span>
                    </p>
                    <p class="mb-0"><small>Les intérêts des mois suivants seront annulés :</small></p>
                    <ul class="mb-0 mt-2">
                        <?php $__currentLoopData = $earlyRepaymentDetails['monthsToCancel']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e(\Carbon\Carbon::parse($month['date'])->format('F Y')); ?> : <?php echo e($calculationService->formatMoney($month['interest'])); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-2">Montant total à payer</h6>
                        <h2 class="mb-0"><?php echo e($calculationService->formatMoney($earlyRepaymentDetails['montantTotal'])); ?></h2>
                        <small class="d-block mt-2">
                            Capital restant (<?php echo e($calculationService->formatMoney($earlyRepaymentDetails['capitalRestant'])); ?>) 
                            + Intérêts dus (<?php echo e($calculationService->formatMoney($earlyRepaymentDetails['interetsDus'])); ?>)
                            <?php if($totalCancelledInterest > 0): ?>
                                - Intérêts déjà annulés (<?php echo e($calculationService->formatMoney($totalCancelledInterest)); ?>)
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <div class="mt-3">
                    <p class="text-muted mb-0">
                        <small>
                            <i class="fas fa-clock me-1"></i>
                            Mois écoulés: <?php echo e($earlyRepaymentDetails['monthsElapsed']); ?> / <?php echo e($earlyRepaymentDetails['totalMonths']); ?>

                        </small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Formulaire de remboursement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('caissiere.loans.early-repayment.process', $loan->loanDocId)); ?>" id="earlyRepaymentForm">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label for="repaymentDate" class="form-label">Date de remboursement <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control <?php $__errorArgs = ['repaymentDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="repaymentDate" 
                               name="repaymentDate" 
                               value="<?php echo e(old('repaymentDate', now()->format('Y-m-d'))); ?>"
                               required
                               onchange="updateCalculation()">
                        <?php $__errorArgs = ['repaymentDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="form-text text-muted">La date de remboursement ne peut pas être antérieure à la date d'octroi (<?php echo e($loan->submitDate->format('d/m/Y')); ?>)</small>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="amount" 
                               name="amount" 
                               step="0.01"
                               value="<?php echo e(old('amount', $earlyRepaymentDetails['montantTotal'])); ?>"
                               required
                               readonly>
                        <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="form-text text-muted">Ce montant est calculé automatiquement pour un remboursement anticipé complet</small>
                    </div>

                    <div class="mb-3">
                        <label for="repaymentTypeIdFk" class="form-label">Type de remboursement <span class="text-danger">*</span></label>
                        <?php if($earlyRepaymentType): ?>
                            <input type="hidden" name="repaymentTypeIdFk" value="<?php echo e($earlyRepaymentType->repaymentTypeID); ?>">
                            <input type="text" 
                                   class="form-control" 
                                   id="repaymentTypeIdFk_display" 
                                   value="<?php echo e($earlyRepaymentType->repaymentName); ?>" 
                                   readonly>
                            <small class="form-text text-muted">Type de remboursement fixé pour un remboursement anticipé</small>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Erreur :</strong> Aucun type de remboursement "Remboursement anticipé" trouvé dans la base de données. 
                                Veuillez contacter l'administrateur pour créer ce type dans la table repaymenttype.
                            </div>
                        <?php endif; ?>
                        <?php $__errorArgs = ['repaymentTypeIdFk'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description (optionnel)</label>
                        <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Remboursement anticipé..."><?php echo e(old('description', 'Remboursement anticipé')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input <?php $__errorArgs = ['confirm_early_repayment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   type="checkbox" 
                                   id="confirm_early_repayment" 
                                   name="confirm_early_repayment" 
                                   value="1"
                                   required>
                            <label class="form-check-label" for="confirm_early_repayment">
                                Je confirme que le membre souhaite effectuer un remboursement anticipé complet. 
                                Les intérêts des mois non échus (<?php echo e(count($earlyRepaymentDetails['monthsToCancel'])); ?> mois) seront annulés.
                            </label>
                            <?php $__errorArgs = ['confirm_early_repayment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Enregistrer le remboursement anticipé
                        </button>
                        <a href="<?php echo e(route('caissiere.loans.show', $loan->loanDocId)); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Résumé -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Résumé</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Montant total dû:</strong>
                    <p class="text-primary h5"><?php echo e($calculationService->formatMoney($totalAmountDue)); ?></p>
                </div>
                <div class="mb-3">
                    <strong>Déjà remboursé:</strong>
                    <p class="text-success"><?php echo e($calculationService->formatMoney($totalRepaid)); ?></p>
                </div>
                <div class="mb-3">
                    <strong>Reste dû:</strong>
                    <p class="text-warning h5"><?php echo e($calculationService->formatMoney($remainingAmount)); ?></p>
                </div>
                <hr>
                <div class="mb-3">
                    <strong>Montant du remboursement anticipé:</strong>
                    <p class="text-primary h4"><?php echo e($calculationService->formatMoney($earlyRepaymentDetails['montantTotal'])); ?></p>
                </div>
                <?php if($earlyRepaymentDetails['interetsACanceler'] > 0): ?>
                <div class="alert alert-success">
                    <strong>Économie pour le membre:</strong>
                    <p class="mb-0 h5"><?php echo e($calculationService->formatMoney($earlyRepaymentDetails['interetsACanceler'])); ?></p>
                    <small>Intérêts annulés</small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Avertissement -->
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Important</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Le remboursement anticipé doit couvrir <strong>tout le capital restant</strong></li>
                    <li>Les intérêts des mois non échus seront <strong>automatiquement annulés</strong></li>
                    <li>Le crédit sera marqué comme <strong>terminé</strong> après l'enregistrement</li>
                    <li>Cette action est <strong>irréversible</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function updateCalculation() {
    // Note: Pour une implémentation complète, on pourrait faire un appel AJAX
    // pour recalculer les montants en fonction de la date sélectionnée
    // Pour l'instant, on garde le montant initial
    const repaymentDate = document.getElementById('repaymentDate').value;
    const loanStartDate = '<?php echo e($loan->submitDate->format('Y-m-d')); ?>';
    
    if (repaymentDate < loanStartDate) {
        alert('La date de remboursement ne peut pas être antérieure à la date d\'octroi du crédit.');
        document.getElementById('repaymentDate').value = '<?php echo e(now()->format('Y-m-d')); ?>';
    }
}

document.getElementById('earlyRepaymentForm').addEventListener('submit', function(e) {
    const confirmCheckbox = document.getElementById('confirm_early_repayment');
    if (!confirmCheckbox.checked) {
        e.preventDefault();
        alert('Veuillez confirmer le remboursement anticipé en cochant la case de confirmation.');
        return false;
    }
    
    return confirm('Êtes-vous sûr de vouloir enregistrer ce remboursement anticipé ? Cette action est irréversible.');
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/caissiere/early-repayment.blade.php ENDPATH**/ ?>