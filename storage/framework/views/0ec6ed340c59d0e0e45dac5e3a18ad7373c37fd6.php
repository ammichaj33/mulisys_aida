<?php $__env->startSection('title', 'Tableau de bord - Gérant'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-tie me-2"></i>Tableau de bord - Gérant</h2>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('members.index')); ?>" class="btn btn-outline-info">
                    <i class="fas fa-users me-2"></i>Liste membres
                </a>
                <a href="<?php echo e(route('gerant.repayments.index')); ?>" class="btn btn-outline-success">
                    <i class="fas fa-money-bill-wave me-2"></i>Remboursements
                </a>
                <a href="<?php echo e(route('gerant.final-validation')); ?>" class="btn btn-primary">
                    <i class="fas fa-gavel me-2"></i>Validation finale
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Messages informatifs permanents -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Rôle :</strong> Gérant - Vous êtes responsable de la validation finale et de la gestion des crédits accordés.
        </div>
    </div>
</div>


<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="<?php echo e(route('gerant.dashboard', ['status' => 'accepted'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'accepted'): ?> border-warning <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e(isset($stats) && isset($stats['accepted']) ? $stats['accepted'] : 0); ?></h3>
                        <p class="mb-0">À valider</p>
                    </div>
                    <i class="fas fa-clock stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?php echo e(route('gerant.dashboard', ['status' => 'validated'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'validated'): ?> border-success <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e(isset($stats) && isset($stats['validated']) ? $stats['validated'] : 0); ?></h3>
                        <p class="mb-0">Validés</p>
                    </div>
                    <i class="fas fa-check-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?php echo e(route('gerant.dashboard', ['status' => 'rejected'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'rejected'): ?> border-danger <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e(isset($stats) && isset($stats['rejected']) ? $stats['rejected'] : 0); ?></h3>
                        <p class="mb-0">Rejetés</p>
                    </div>
                    <i class="fas fa-times-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?php echo e(route('gerant.dashboard', ['status' => 'done'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'done'): ?> border-info <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e(isset($stats) && isset($stats['done']) ? $stats['done'] : 0); ?></h3>
                        <p class="mb-0">Terminés</p>
                    </div>
                    <i class="fas fa-flag-checkered stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Statistiques financières -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-success">
                    <i class="fas fa-money-bill-wave me-2"></i>Remboursements aujourd'hui
                </h5>
                <h3 class="text-success"><?php echo e(number_format($todayRepayments['total'] ?? 0, 2, ',', ' ')); ?> USD</h3>
                <small class="text-muted"><?php echo e($todayRepayments['count'] ?? 0); ?> transaction(s)</small>
                <div class="mt-2">
                    <small class="text-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Date: <?php echo e(date('Y-m-d')); ?>

                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-info">
                    <i class="fas fa-calendar-week me-2"></i>Cette semaine
                </h5>
                <h3 class="text-info"><?php echo e(number_format($weekRepayments['total'] ?? 0, 2, ',', ' ')); ?> USD</h3>
                <small class="text-muted"><?php echo e($weekRepayments['count'] ?? 0); ?> transaction(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-primary">
                    <i class="fas fa-calendar-alt me-2"></i>Ce mois
                </h5>
                <h3 class="text-primary"><?php echo e(number_format($monthRepayments['total'] ?? 0, 2, ',', ' ')); ?> USD</h3>
                <small class="text-muted"><?php echo e($monthRepayments['count'] ?? 0); ?> transaction(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Pénalités en attente
                </h5>
                <h3 class="text-warning"><?php echo e(number_format($pendingPenalties['total'] ?? 0, 2, ',', ' ')); ?> USD</h3>
                <small class="text-muted"><?php echo e($pendingPenalties['count'] ?? 0); ?> pénalité(s)</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('gerant.dashboard')); ?>" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <?php $__currentLoopData = ['validated' => 'À valider', 'done' => 'Approuvé', 'rejected' => 'Rejeté', 'finalized' => 'Finalisé']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e((request('status') == $key) ? 'selected' : ''); ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo e(request('search')); ?>" 
                               placeholder="Référence, nom, téléphone...">
                    </div>

                    <div class="w-100"></div>

                    <div class="col-md-2">
                        <label for="overdue" class="form-label">Insolvables</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="overdue" name="overdue" <?php echo e(request('overdue') ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="overdue">
                                Date prévue dépassée
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="overdue_from" class="form-label">Du</label>
                        <input type="date" class="form-control" id="overdue_from" name="overdue_from"
                               value="<?php echo e(request('overdue_from')); ?>"
                               <?php if(!request('overdue')): ?> disabled <?php endif; ?>>
                    </div>
                    <div class="col-md-2">
                        <label for="overdue_to" class="form-label">Au</label>
                        <input type="date" class="form-control" id="overdue_to" name="overdue_to"
                               value="<?php echo e(request('overdue_to')); ?>"
                               <?php if(!request('overdue')): ?> disabled <?php endif; ?>>
                    </div>

                    <div class="w-100"></div>

                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <?php if(request('overdue')): ?>
                                <a href="<?php echo e(route('gerant.dashboard.insolvables.pdf', request()->query())); ?>" target="_blank" class="btn btn-outline-dark">
                                    <i class="fas fa-print me-2"></i>Imprimer
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('gerant.dashboard')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const overdue = document.getElementById('overdue');
    const from = document.getElementById('overdue_from');
    const to = document.getElementById('overdue_to');
    if (!overdue || !from || !to) return;
    const sync = () => {
        const enabled = overdue.checked;
        from.disabled = !enabled;
        to.disabled = !enabled;
        if (!enabled) {
            from.value = '';
            to.value = '';
        }
    };
    overdue.addEventListener('change', sync);
    sync();
});
</script>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Demandes à valider
                    <?php if($recentRequests->count() > 0): ?>
                        <span class="badge bg-primary ms-2"><?php echo e($recentRequests->count()); ?> résultat(s)</span>
                    <?php endif; ?>
                </h5>
                <?php if(!empty(request('status')) || !empty(request('search'))): ?>
                    <div class="text-muted">
                        <small>Filtres actifs</small>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if($recentRequests->isEmpty()): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune demande à valider</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Membre</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr <?php if($loan->status == 'validated'): ?> class="table-warning" <?php endif; ?>>
                                        <td>
                                            <strong><?php echo e(htmlspecialchars($loan->refNumber)); ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if($loan->member->photo): ?>
                                                    <img src="<?php echo e(route('members.photo', $loan->member->memberId)); ?>" 
                                                         alt="Photo de <?php echo e($loan->member->firstName); ?> <?php echo e($loan->member->lastName); ?>" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 35px; height: 35px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo e(htmlspecialchars($loan->member->firstName . ' ' . $loan->member->lastName)); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo e(htmlspecialchars($loan->member->phoneNumber)); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo e(number_format($loan->requestAmount, 2, ',', ' ')); ?> USD</td>
                                        <td>
                                            <?php
                                                $statusClass = '';
                                                switch ($loan->status) {
                                                    case 'draft': $statusClass = 'bg-secondary'; break;
                                                    case 'accepted': $statusClass = 'bg-primary'; break;
                                                    case 'validated': $statusClass = 'bg-warning'; break;
                                                    case 'done': $statusClass = 'bg-success'; break;
                                                    case 'rejected': $statusClass = 'bg-danger'; break;
                                                    case 'finalized': $statusClass = 'bg-info'; break;
                                                    default: $statusClass = 'bg-light text-dark'; break;
                                                }
                                            ?>
                                            <span class="badge <?php echo e($statusClass); ?>">
                                                <?php
                                                    $statusLabels = [
                                                        'draft' => 'Brouillon',
                                                        'accepted' => 'Accepté',
                                                        'validated' => 'Validé',
                                                        'done' => 'Approuvé',
                                                        'rejected' => 'Rejeté',
                                                        'finalized' => 'Finalisé'
                                                    ];
                                                    $statusLabel = $statusLabels[$loan->status] ?? ucfirst($loan->status);
                                                ?>
                                                <?php echo e($statusLabel); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e(date('d/m/Y', strtotime($loan->submitDate))); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('gerant.loans.show', $loan->loanDocId)); ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/gerant/dashboard.blade.php ENDPATH**/ ?>