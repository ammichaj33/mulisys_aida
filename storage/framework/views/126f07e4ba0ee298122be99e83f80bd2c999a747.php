

<?php $__env->startSection('title', 'Détails de la pénalité'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-exclamation-triangle me-2"></i>Détails de la pénalité</h2>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('penalties.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
                <?php if($penalty->status === 'notPaid'): ?>
                    <button onclick="payPenalty(<?php echo e($penalty->penalityId); ?>, <?php echo e($penalty->amount); ?>)" 
                            class="btn btn-success">
                        <i class="fas fa-money-bill-wave me-2"></i>Payer la pénalité
                    </button>
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
                                <td><?php echo e($penalty->penaltyMonth); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Montant :</strong></td>
                                <td><span class="text-danger fw-bold h5"><?php echo e(round($penalty->amount, 2)); ?> USD</span></td>
                            </tr>
                            <tr>
                                <td><strong>Statut :</strong></td>
                                <td>
                                    <?php if($penalty->status === 'paid'): ?>
                                        <span class="badge bg-success fs-6">Payée</span>
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
                
                <?php if($penalty->description): ?>
                    <div class="mt-3">
                        <strong>Description :</strong>
                        <p class="mt-2 p-3 bg-light rounded"><?php echo e($penalty->description); ?></p>
                    </div>
                <?php endif; ?>
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
                        <h6 class="text-primary">Formule générale :</h6>
                        <div class="bg-light p-3 rounded mb-3">
                            <code class="fs-5">
                                <strong>Pénalité = Capital restant × Taux mensuel × Mois de retard</strong>
                            </code>
                        </div>
                        
                        <h6 class="text-primary">Paramètres utilisés :</h6>
                        <ul class="list-unstyled">
                            <li><strong>Taux mensuel :</strong> <?php echo e(config('penalties.penalty_monthly_rate', 1.0)); ?>% par mois</li>
                            <li><strong>Jours de tolérance :</strong> <?php echo e(config('penalties.tolerance_days', 5)); ?> jours</li>
                            <li><strong>Base de calcul :</strong> Capital restant dû</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <?php if($penaltyDetails): ?>
                            <h6 class="text-primary">Calcul détaillé de cette pénalité :</h6>
                            <div class="bg-light p-3 rounded">
                                <p><strong>Données de cette pénalité :</strong></p>
                                <ul class="mb-2">
                                    <li>Date d'échéance : <strong><?php echo e($penaltyDetails['due_date']->format('d/m/Y')); ?></strong></li>
                                    <li>Montant attendu : <strong><?php echo e(round($penaltyDetails['expected_amount'], 2)); ?> USD</strong></li>
                                    <li>Montant remboursé avant échéance : <strong><?php echo e(round($penaltyDetails['total_repaid_before_due'], 2)); ?> USD</strong></li>
                                    <li>Capital restant dû : <strong><?php echo e(round($penaltyDetails['remaining_capital'], 2)); ?> USD</strong></li>
                                    <li>Mois de retard : <strong><?php echo e($penaltyDetails['months_overdue']); ?> mois</strong></li>
                                    <li>Taux mensuel : <strong><?php echo e($penaltyDetails['monthly_rate']); ?>%</strong></li>
                                </ul>
                                <p><strong>Calcul :</strong></p>
                                <p class="mb-0">
                                    <code>Pénalité = <?php echo e(round($penaltyDetails['remaining_capital'], 2)); ?> × <?php echo e($penaltyDetails['monthly_rate']); ?>% × <?php echo e($penaltyDetails['months_overdue']); ?> = <?php echo e(round($penaltyDetails['calculated_penalty'], 2)); ?> USD</code>
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
                            <h6 class="text-success">Avantages du calcul mensuel :</h6>
                            <ul class="small">
                                <li>Calcul simple et transparent</li>
                                <li>Pénalité proportionnelle au retard</li>
                                <li>Prise en compte des paiements partiels</li>
                                <li>Équitable pour l'emprunteur</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
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

<!-- Modal de paiement -->
<div class="modal fade" id="payPenaltyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payer la pénalité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="payPenaltyForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Date de paiement <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" 
                               value="<?php echo e(date('Y-m-d')); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer le paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function payPenalty(penaltyId, amount) {
    document.getElementById('amount').value = amount;
    document.getElementById('payPenaltyForm').action = '<?php echo e(route("penalties.pay", ":id")); ?>'.replace(':id', penaltyId);
    
    const modal = new bootstrap.Modal(document.getElementById('payPenaltyModal'));
    modal.show();
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/penalties/show.blade.php ENDPATH**/ ?>