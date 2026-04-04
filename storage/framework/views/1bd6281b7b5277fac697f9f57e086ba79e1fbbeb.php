

<?php $__env->startSection('title', 'Tableau de bord - Caissière'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-cash-register me-2"></i>Tableau de bord - Caissière</h2>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('members.index')); ?>" class="btn btn-outline-info">
                    <i class="fas fa-users me-2"></i>Liste membres
                </a>
                <a href="<?php echo e(route('penalties.index')); ?>" class="btn btn-outline-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Pénalités
                </a>
                <a href="<?php echo e(route('caissiere.repayments.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau remboursement
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
            <strong>Rôle :</strong> Caissière - Vous êtes responsable de l'enregistrement des remboursements et de la gestion des pénalités.
        </div>
    </div>
</div>


<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <a href="<?php echo e(route('caissiere.dashboard', ['status' => 'draft'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'draft'): ?> border-secondary <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e($stats['draft']); ?></h3>
                        <p class="mb-0">Brouillons</p>
                    </div>
                    <i class="fas fa-edit stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="<?php echo e(route('caissiere.dashboard', ['status' => 'accepted'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'accepted'): ?> border-warning <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e($stats['accepted']); ?></h3>
                        <p class="mb-0">À valider</p>
                    </div>
                    <i class="fas fa-clock stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="<?php echo e(route('caissiere.dashboard', ['status' => 'validated'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'validated'): ?> border-primary <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e($stats['validated']); ?></h3>
                        <p class="mb-0">Validés</p>
                    </div>
                    <i class="fas fa-check-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="<?php echo e(route('caissiere.dashboard', ['status' => 'rejected'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'rejected'): ?> border-danger <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e($stats['rejected']); ?></h3>
                        <p class="mb-0">Rejetés</p>
                    </div>
                    <i class="fas fa-times-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="<?php echo e(route('caissiere.dashboard', ['status' => 'done'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'done'): ?> border-success <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e($stats['done']); ?></h3>
                        <p class="mb-0">Terminés</p>
                    </div>
                    <i class="fas fa-flag-checkered stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?php echo e($todayRepayments['count'] ?? 0); ?></h3>
                    <p class="mb-0">Remboursements aujourd'hui</p>
                </div>
                <i class="fas fa-calendar-day stats-icon"></i>
            </div>
        </div>
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
                <h3 class="text-success"><?php echo e(round($todayRepayments['total'] ?? 0, 2)); ?> USD</h3>
                <small class="text-muted"><?php echo e($todayRepayments['count'] ?? 0); ?> transaction(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title text-info">
                    <i class="fas fa-calendar-week me-2"></i>Cette semaine
                </h5>
                <h3 class="text-info"><?php echo e(round($weekRepayments['total'] ?? 0, 2)); ?> USD</h3>
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
                <h3 class="text-primary"><?php echo e(round($monthRepayments['total'] ?? 0, 2)); ?> USD</h3>
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
                <h3 class="text-warning"><?php echo e(round($pendingPenalties['total'] ?? 0, 2)); ?> USD</h3>
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
                <form method="GET" action="<?php echo e(route('caissiere.dashboard')); ?>" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <?php $__currentLoopData = ['draft' => 'Brouillon', 'accepted' => 'À valider', 'validated' => 'Validé', 'rejected' => 'Rejeté', 'done' => 'Terminé']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e((request('status') == $key) ? 'selected' : ''); ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo e(request('search')); ?>" 
                               placeholder="Référence, nom, téléphone...">
                    </div>
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
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="<?php echo e(route('caissiere.dashboard')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Tous les dossiers
                    <?php if($recentRequests->count() > 0): ?>
                        <span class="badge bg-primary ms-2"><?php echo e($recentRequests->total()); ?> résultat(s)</span>
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
                        <p class="text-muted">Aucun crédit à gérer</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Membre</th>
                                    <th>Montant</th>
                                    <th>Intérêts</th>
                                    <th>Statut</th>
                                    <th>Remboursé</th>
                                    <th>Reste dû</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e(htmlspecialchars($request->refNumber)); ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if($request->member->photo): ?>
                                                    <img src="<?php echo e(route('members.photo', $request->member->memberId)); ?>" 
                                                         alt="Photo de <?php echo e($request->member->firstName); ?> <?php echo e($request->member->lastName); ?>" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 35px; height: 35px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo e(htmlspecialchars($request->member->firstName . ' ' . $request->member->lastName)); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo e(htmlspecialchars($request->member->phoneNumber)); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo e(round($request->requestAmount, 2)); ?> USD</td>
                                        <td><?php echo e(round($request->interestAmount ?? 0, 2)); ?> USD</td>
                                        <td>
                                            <?php
                                                $statusLabels = [
                                                    'draft' => 'Brouillon',
                                                    'accepted' => 'À valider',
                                                    'validated' => 'Validé',
                                                    'rejected' => 'Rejeté',
                                                    'done' => 'Terminé'
                                                ];
                                                $statusClass = [
                                                    'draft' => 'bg-secondary',
                                                    'accepted' => 'bg-warning',
                                                    'validated' => 'bg-primary',
                                                    'rejected' => 'bg-danger',
                                                    'done' => 'bg-success'
                                                ];
                                                $statusLabel = $statusLabels[$request->status] ?? ucfirst($request->status);
                                                $statusClassValue = $statusClass[$request->status] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo e($statusClassValue); ?>">
                                                <?php echo e($statusLabel); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success"><?php echo e(round($request->loan_repayments_sum_amount ?? 0, 2)); ?> USD</span>
                                        </td>
                                        <td>
                                            <span class="text-<?php echo e(($request->remainingAmount ?? 0) > 0 ? 'danger' : 'success'); ?>">
                                                <?php echo e(round($request->remainingAmount ?? 0, 2)); ?> USD
                                            </span>
                                        </td>
                                        <td><?php echo e(date('d/m/Y', strtotime($request->createdAt))); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('caissiere.loans.show', $request->loanDocId)); ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        <?php if(request('overdue')): ?>
                            <?php echo e($recentRequests->links()); ?>

                        <?php else: ?>
                            <?php echo e($recentRequests->appends(request()->query())->links()); ?>

                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/caissiere/dashboard.blade.php ENDPATH**/ ?>