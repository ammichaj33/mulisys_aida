

<?php $__env->startSection('title', 'Gestion des pénalités'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-exclamation-triangle me-2"></i>Gestion des pénalités</h2>
            <div class="d-flex gap-2">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-penalties')): ?>
                    <form method="POST" action="<?php echo e(route('penalties.recalculate-all')); ?>" class="d-inline"
                          onsubmit="return confirm('Recalculer les pénalités pour tous les crédits ?\n\nSupprimez d\'abord les pénalités obsolètes en base si nécessaire.')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sync-alt me-2"></i>Recalculer les pénalités
                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?php echo e(route('caissiere.dashboard')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?php echo e($stats['total']); ?></h3>
                    <p class="mb-0">Total pénalités</p>
                </div>
                <i class="fas fa-list stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0 text-warning"><?php echo e($stats['pending']); ?></h3>
                    <p class="mb-0">En attente</p>
                </div>
                <i class="fas fa-clock stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0 text-success"><?php echo e($stats['paid']); ?></h3>
                    <p class="mb-0">Payées</p>
                </div>
                <i class="fas fa-check-circle stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0 text-danger"><?php echo e(round($stats['total_amount'], 2)); ?> USD</h3>
                    <p class="mb-0">Montant en attente</p>
                </div>
                <i class="fas fa-dollar-sign stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0 text-success"><?php echo e(round($stats['paid_amount'], 2)); ?> USD</h3>
                    <p class="mb-0">Montant payé</p>
                </div>
                <i class="fas fa-money-bill-wave stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0 text-primary"><?php echo e(round($stats['total_amount'] + $stats['paid_amount'], 2)); ?> USD</h3>
                    <p class="mb-0">Total généré</p>
                </div>
                <i class="fas fa-chart-line stats-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques financières -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Pénalités en attente
                </h5>
                <h3 class="text-warning"><?php echo e(round($stats['total_amount'], 2)); ?> USD</h3>
                <small class="text-muted"><?php echo e($stats['pending']); ?> pénalité(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-success">
                    <i class="fas fa-check-circle me-2"></i>Pénalités payées
                </h5>
                <h3 class="text-success"><?php echo e(round($stats['paid_amount'], 2)); ?> USD</h3>
                <small class="text-muted"><?php echo e($stats['paid']); ?> pénalité(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-primary">
                    <i class="fas fa-chart-line me-2"></i>Total généré
                </h5>
                <h3 class="text-primary"><?php echo e(round($stats['total_amount'] + $stats['paid_amount'], 2)); ?> USD</h3>
                <small class="text-muted"><?php echo e($stats['total']); ?> pénalité(s) au total</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="background-color: white;">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('penalties.index')); ?>" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="notPaid" <?php echo e((request('status') == 'notPaid') ? 'selected' : ''); ?>>En attente</option>
                            <option value="paid" <?php echo e((request('status') == 'paid') ? 'selected' : ''); ?>>Payées</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo e(request('search')); ?>" 
                               placeholder="Raison, référence, nom du membre...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="<?php echo e(route('penalties.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Liste des pénalités -->
<div class="row">
    <div class="col-12">
        <div class="card" style="background-color: white;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Liste des pénalités
                    <?php if($penalties->count() > 0): ?>
                        <span class="badge bg-primary ms-2"><?php echo e($penalties->total()); ?> résultat(s)</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if($penalties->isEmpty()): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Aucune pénalité trouvée</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Membre</th>
                                    <th>Mois</th>
                                    <th>Montant</th>
                                    <th>Raison</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $penalties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $penalty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($penalty->loanDoc->refNumber); ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if($penalty->loanDoc->member->photo): ?>
                                                    <img src="<?php echo e(route('members.photo', $penalty->loanDoc->member->memberId)); ?>" 
                                                         alt="Photo de <?php echo e($penalty->loanDoc->member->firstName); ?> <?php echo e($penalty->loanDoc->member->lastName); ?>" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 35px; height: 35px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo e($penalty->loanDoc->member->firstName); ?> <?php echo e($penalty->loanDoc->member->lastName); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo e($penalty->loanDoc->member->phoneNumber); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo e($penalty->penaltyMonth); ?></td>
                                        <td>
                                            <span class="text-danger fw-bold"><?php echo e(round($penalty->amount, 2)); ?> USD</span>
                                        </td>
                                        <td>
                                            <small><?php echo e($penalty->reason); ?></small>
                                        </td>
                                        <td>
                                            <?php if($penalty->status === 'paid'): ?>
                                                <span class="badge bg-success">Payée</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">En attente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo e($penalty->created_at->format('d/m/Y H:i')); ?>

                                            </small>
                                        </td>
                                        <td>
                                            <a href="<?php echo e(route('penalties.show', $penalty->penalityId)); ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($penalty->status === 'notPaid'): ?>
                                                <button onclick="payPenalty(<?php echo e($penalty->penalityId); ?>, <?php echo e($penalty->amount); ?>)" 
                                                        class="btn btn-sm btn-success" title="Payer">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        <?php echo e($penalties->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
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
                        <label for="amount" class="form-label">Montant à payer <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" required>
                        <small class="text-muted" id="amountHelp">Entre 0,01 et le montant dû maximum.</small>
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
    const modalEl = document.getElementById('payPenaltyModal');
    const amountInput = document.getElementById('amount');
    const roundedAmount = Math.round(parseFloat(amount) * 100) / 100;

    amountInput.value = roundedAmount;
    amountInput.min = 0.01;
    amountInput.max = roundedAmount;
    document.getElementById('amountHelp').textContent = 'Entre 0,01 et ' + roundedAmount.toFixed(2) + ' USD maximum.';
    document.getElementById('payPenaltyForm').action = <?php echo json_encode(url('penalties'), 15, 512) ?> + '/' + penaltyId + '/pay';

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

document.getElementById('payPenaltyModal').addEventListener('hidden.bs.modal', function () {
    document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/penalties/index.blade.php ENDPATH**/ ?>