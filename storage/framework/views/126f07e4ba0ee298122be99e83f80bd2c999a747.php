

<?php $__env->startSection('title', 'Détails de la pénalité'); ?>

<?php $__env->startSection('content'); ?>
<?php if($penalty->isPartiallyPaid()): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Paiement partiel : <?php echo e(round($penalty->paidAmount, 2)); ?> USD payés sur <?php echo e(round($penalty->total_amount, 2)); ?> USD.
        Il reste <?php echo e(round($penalty->remaining_amount, 2)); ?> USD à régulariser.
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-exclamation-triangle me-2"></i>Détails de la pénalité</h2>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('penalties.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
                <?php if($penalty->status === 'notPaid'): ?>
                    <a href="#pay-penalty-form" class="btn btn-success">
                        <i class="fas fa-money-bill-wave me-2"></i>Payer la pénalité
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations de la pénalité</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID de la pénalité :</strong></td>
                                <td><?php echo e($penalty->penalityId); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Mois concerné :</strong></td>
                                <td>
                                    <?php
                                        $displayMonth = $penalty->penaltyMonth;
                                        if (preg_match('/^(\d{2,4})-(\d{2})$/', $displayMonth, $m) && (int) $m[1] < 100) {
                                            $displayMonth = (2000 + (int) $m[1]) . '-' . $m[2];
                                        }
                                    ?>
                                    <?php echo e($displayMonth); ?>

                                </td>
                            </tr>
                            <tr>
                                <td><strong>Montant total :</strong></td>
                                <td><span class="fw-bold h5"><?php echo e(round($penalty->total_amount, 2)); ?> USD</span></td>
                            </tr>
                            <?php if($penalty->isPartiallyPaid()): ?>
                                <tr>
                                    <td><strong>Reste à payer :</strong></td>
                                    <td><span class="text-danger fw-bold"><?php echo e(round($penalty->remaining_amount, 2)); ?> USD</span></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Statut :</strong></td>
                                <td>
                                    <?php if($penalty->status === 'paid'): ?>
                                        <span class="badge bg-success fs-6">Payée</span>
                                    <?php elseif($penalty->isPartiallyPaid()): ?>
                                        <span class="badge bg-warning fs-6">Paiement partiel</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning fs-6">En attente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Raison :</strong></td>
                                <td><?php echo e(ucfirst($penalty->reason)); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Date de création :</strong></td>
                                <td><?php echo e($penalty->created_at->format('d/m/Y H:i')); ?></td>
                            </tr>
                            <?php if($penalty->paidAt): ?>
                                <tr>
                                    <td><strong>Date de paiement :</strong></td>
                                    <td><?php echo e($penalty->paidAt->format('d/m/Y H:i')); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Montant payé :</strong></td>
                                    <td><span class="text-success fw-bold"><?php echo e(round($penalty->paidAmount, 2)); ?> USD</span></td>
                                </tr>
                            <?php endif; ?>
                            <?php if($penalty->creator): ?>
                                <tr>
                                    <td><strong>Créée par :</strong></td>
                                    <td><?php echo e($penalty->creator->fullName); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <strong>Résumé du retard :</strong>
                    <?php if($penaltyDetails): ?>
                        <div class="mt-2 p-3 bg-light rounded">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:40%">Échéance concernée</td>
                                    <td>
                                        <strong>n°<?php echo e($penaltyDetails['installment_number']); ?></strong>
                                        du <?php echo e($penaltyDetails['due_date']->format('d/m/Y')); ?>

                                        (<?php echo e(round($penaltyDetails['expected_amount'], 2)); ?> USD)
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Type de retard</td>
                                    <td>
                                        <?php if($penaltyDetails['is_consecutive']): ?>
                                            <span class="badge bg-warning text-dark">Retard consécutif</span>
                                            <small class="text-muted d-block mt-1">
                                                Le mois précédent n'était pas encore soldé.
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-info">Premier retard</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if($penaltyDetails['is_consecutive']): ?>
                                    <tr>
                                        <td class="text-muted">Impayés reportés<br><small>(tous les mois précédents)</small></td>
                                        <td>
                                            <?php if(!empty($penaltyDetails['previous_remaining']['breakdown'])): ?>
                                                <ul class="mb-2 ps-3">
                                                    <?php $__currentLoopData = $penaltyDetails['previous_remaining']['breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li>
                                                            Échéance n°<?php echo e($month['installment_number']); ?>

                                                            (<?php echo e($month['due_date']->format('d/m/Y')); ?>) :
                                                            <strong><?php echo e(round($month['subtotal'], 2)); ?> USD</strong>
                                                            <small class="text-muted d-block">
                                                                <?php echo e(round($month['installment_remaining'], 2)); ?> échéance
                                                                + <?php echo e(round($month['penalty_remaining'], 2)); ?> pénalité
                                                            </small>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            <?php endif; ?>
                                            <div>
                                                Total échéances impayées :
                                                <strong><?php echo e(round($penaltyDetails['previous_remaining']['installment_remaining'], 2)); ?> USD</strong>
                                            </div>
                                            <div>
                                                Total pénalités impayées :
                                                <strong><?php echo e(round($penaltyDetails['previous_remaining']['penalty_remaining'], 2)); ?> USD</strong>
                                            </div>
                                            <div class="mt-1">
                                                <strong>Total reporté : <?php echo e(round($penaltyDetails['previous_remaining']['total'], 2)); ?> USD</strong>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Montant de la pénalité</td>
                                    <td><strong class="text-danger"><?php echo e(round($penalty->remaining_amount > 0 ? $penalty->remaining_amount : $penalty->total_amount, 2)); ?> USD</strong></td>
                                </tr>
                            </table>
                        </div>
                    <?php elseif($penalty->description): ?>
                        <p class="mt-2 p-3 bg-light rounded mb-0" style="white-space: pre-line;"><?php echo e($penalty->description); ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-muted mb-0">Aucun détail disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Formule de calcul de la pénalité -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Formule de calcul de la pénalité</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Formules appliquées :</h6>
                        <div class="bg-light p-3 rounded mb-3">
                            <p class="mb-2"><code><strong>Premier retard :</strong> (Amortissement + Intérêt) × <?php echo e(config('penalties.penalty_rate', 10)); ?>%</code></p>
                            <p class="mb-0"><code><strong>Retard consécutif :</strong> (Reste cumulé M-1…M-(n-1) + Amortissement + Intérêt) × <?php echo e(config('penalties.penalty_rate', 10)); ?>%</code></p>
                        </div>
                        
                        <h6 class="text-primary">Paramètres utilisés :</h6>
                        <ul class="list-unstyled">
                            <li><strong>Taux de pénalité :</strong> <?php echo e(config('penalties.penalty_rate', 10)); ?>%</li>
                            <li><strong>Jours de tolérance :</strong> <?php echo e(config('penalties.tolerance_days', 30)); ?> jours</li>
                            <li><strong>Reste cumulé :</strong> Somme des échéances et pénalités impayées de tous les mois précédents</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <?php if($penaltyDetails): ?>
                            <h6 class="text-primary">Calcul détaillé de cette pénalité :</h6>
                            <div class="bg-light p-3 rounded">
                                <p><strong>Données de cette pénalité :</strong></p>
                                <ul class="mb-2">
                                    <li>Date d'échéance : <strong><?php echo e($penaltyDetails['due_date']->format('d/m/Y')); ?></strong></li>
                                    <li>Montant échéance : <strong><?php echo e(round($penaltyDetails['expected_amount'], 2)); ?> USD</strong></li>
                                    <li>Total remboursé : <strong><?php echo e(round($penaltyDetails['total_repaid'], 2)); ?> USD</strong></li>
                                    <li>Amortissement mois : <strong><?php echo e(round($penaltyDetails['current_month_capital'], 2)); ?> USD</strong></li>
                                    <li>Intérêt mois : <strong><?php echo e(round($penaltyDetails['current_month_interest'], 2)); ?> USD</strong></li>
                                    <?php if($penaltyDetails['is_consecutive']): ?>
                                        <li>Total échéances impayées (mois précédents) : <strong><?php echo e(round($penaltyDetails['previous_remaining']['installment_remaining'], 2)); ?> USD</strong></li>
                                        <li>Total pénalités impayées (mois précédents) : <strong><?php echo e(round($penaltyDetails['previous_remaining']['penalty_remaining'], 2)); ?> USD</strong></li>
                                        <li>Reste cumulé reporté : <strong><?php echo e(round($penaltyDetails['previous_remaining']['total'], 2)); ?> USD</strong></li>
                                    <?php endif; ?>
                                    <li>Mois de retard : <strong><?php echo e($penaltyDetails['months_overdue']); ?> mois</strong></li>
                                    <li>Taux : <strong><?php echo e($penaltyDetails['penalty_rate']); ?>%</strong></li>
                                </ul>
                                <p><strong>Calcul :</strong></p>
                                <p class="mb-0">
                                    <code>Pénalité = <?php echo e($penaltyDetails['formula']); ?> = <?php echo e(round($penaltyDetails['calculated_penalty'], 2)); ?> USD</code>
                                </p>
                            </div>
                        <?php else: ?>
                            <h6 class="text-primary">Exemple de calcul :</h6>
                            <div class="bg-light p-3 rounded">
                                <p><strong>Si :</strong></p>
                                <ul class="mb-2">
                                    <li>Capital restant dû : <strong>200 USD</strong></li>
                                    <li>Mois de retard : <strong>1 mois</strong></li>
                                    <li>Taux mensuel : <strong>1%</strong></li>
                                </ul>
                                <p><strong>Alors :</strong></p>
                                <p class="mb-0">
                                    <code>Pénalité = 200 × 1% × 1 = 2.00 USD</code>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <h6 class="text-success">Logique appliquée :</h6>
                            <ul class="small">
                                <li>Le <strong>reste cumulé</strong> de tous les mois précédents entre dans le calcul</li>
                                <li>Chaque mois : échéance impayée + pénalité impayée de ce mois</li>
                                <li>Les paiements partiels réduisent le reste échéance mois par mois</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <?php if($penalty->status === 'notPaid'): ?>
        <div class="card mb-4 border-success" id="pay-penalty-form">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payer la pénalité</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('penalties.pay', $penalty->penalityId)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if($errors->has('amount') || $errors->has('payment_date')): ?>
                        <div class="alert alert-danger py-2">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><?php echo e($error); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                    <?php if($penalty->isPartiallyPaid()): ?>
                        <div class="alert alert-info py-2 mb-3">
                            Déjà payé : <strong><?php echo e(round($penalty->paidAmount, 2)); ?> USD</strong><br>
                            Reste à payer : <strong><?php echo e(round($penalty->remaining_amount, 2)); ?> USD</strong>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant à payer <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" max="<?php echo e(round($penalty->remaining_amount, 2)); ?>"
                               class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="amount" name="amount"
                               value="<?php echo e(old('amount', round($penalty->remaining_amount, 2))); ?>" required>
                        <small class="text-muted">Entre 0,01 et <?php echo e(round($penalty->remaining_amount, 2)); ?> USD maximum.</small>
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
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Date de paiement <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date"
                               value="<?php echo e(old('payment_date', date('Y-m-d'))); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check me-2"></i>Confirmer le paiement
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Informations du crédit -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Crédit associé</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Référence :</strong></td>
                        <td><?php echo e($penalty->loanDoc->refNumber); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Montant :</strong></td>
                        <td><?php echo e(round($penalty->loanDoc->requestAmount, 2)); ?> USD</td>
                    </tr>
                    <tr>
                        <td><strong>Durée :</strong></td>
                        <td><?php echo e($penalty->loanDoc->loanMonths); ?> mois</td>
                    </tr>
                    <tr>
                        <td><strong>Taux :</strong></td>
                        <td><?php echo e($penalty->loanDoc->interestRate); ?>%</td>
                    </tr>
                    <tr>
                        <td><strong>Statut :</strong></td>
                        <td>
                            <?php switch($penalty->loanDoc->status):
                                case ('draft'): ?>
                                    <span class="badge bg-secondary">Brouillon</span>
                                    <?php break; ?>
                                <?php case ('accepted'): ?>
                                    <span class="badge bg-warning">À valider</span>
                                    <?php break; ?>
                                <?php case ('validated'): ?>
                                    <span class="badge bg-success">Validé</span>
                                    <?php break; ?>
                                <?php case ('rejected'): ?>
                                    <span class="badge bg-danger">Rejeté</span>
                                    <?php break; ?>
                                <?php case ('done'): ?>
                                    <span class="badge bg-info">Terminé</span>
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="<?php echo e(route('loans.show', $penalty->loanDoc->loanDocId)); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-2"></i>Voir le crédit
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Informations du membre -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Membre</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <?php if($penalty->loanDoc->member->photo): ?>
                        <img src="<?php echo e(route('members.photo', $penalty->loanDoc->member->memberId)); ?>" 
                             alt="Photo de <?php echo e($penalty->loanDoc->member->firstName); ?> <?php echo e($penalty->loanDoc->member->lastName); ?>" 
                             class="rounded-circle me-3" 
                             style="width: 50px; height: 50px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                             style="width: 50px; height: 50px;">
                            <i class="fas fa-user text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <strong><?php echo e($penalty->loanDoc->member->firstName); ?> <?php echo e($penalty->loanDoc->member->lastName); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo e($penalty->loanDoc->member->phoneNumber); ?></small>
                    </div>
                </div>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Email :</strong></td>
                        <td><?php echo e($penalty->loanDoc->member->email ?? 'Non renseigné'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Adresse :</strong></td>
                        <td><?php echo e($penalty->loanDoc->member->address ?? 'Non renseignée'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Institution :</strong></td>
                        <td><?php echo e($penalty->loanDoc->member->institutionFrom ?? 'Non renseignée'); ?></td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="<?php echo e(route('members.show', $penalty->loanDoc->member->memberId)); ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-user me-2"></i>Voir le membre
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/penalties/show.blade.php ENDPATH**/ ?>