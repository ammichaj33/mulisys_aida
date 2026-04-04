

<?php $__env->startSection('title', 'Tableau de bord - Réceptionniste'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</h2>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('members.index')); ?>" class="btn btn-outline-info">
                    <i class="fas fa-users me-2"></i>Liste membres
                </a>
                <a href="<?php echo e(route('receptionniste.loans.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvelle demande
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
            <strong>Rôle :</strong> Réceptionniste - Vous êtes responsable de l'enregistrement des demandes de crédit et de la gestion des membres.
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="<?php echo e(route('receptionniste.dashboard', ['status' => 'draft'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'draft'): ?> border-primary <?php endif; ?>">
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
    <div class="col-md-3 mb-3">
        <a href="<?php echo e(route('receptionniste.dashboard', ['status' => 'accepted'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'accepted'): ?> border-success <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e($stats['accepted']); ?></h3>
                        <p class="mb-0">Acceptés</p>
                    </div>
                    <i class="fas fa-check-circle stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?php echo e(route('receptionniste.dashboard', ['status' => 'validated'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'validated'): ?> border-info <?php endif; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo e($stats['validated']); ?></h3>
                        <p class="mb-0">Validés</p>
                    </div>
                    <i class="fas fa-thumbs-up stats-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?php echo e(route('receptionniste.dashboard', ['status' => 'done'])); ?>" class="text-decoration-none">
            <div class="card stats-card <?php if(request('status') == 'done'): ?> border-warning <?php endif; ?>">
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
</div>

<!-- Filtres -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <?php $__currentLoopData = ['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="<?php echo e(route('receptionniste.dashboard')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Demandes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Demandes
                    <?php if($recentRequests->total() > 0): ?>
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
                        <p class="text-muted">Aucune demande enregistrée</p>
                        <a href="<?php echo e(route('receptionniste.loans.create')); ?>" class="btn btn-primary">Créer la première demande</a>
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
                                <?php $__currentLoopData = $recentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr <?php if($request->status == 'toreviewed'): ?> class="table-success" <?php endif; ?>>
                                        <td>
                                            <strong><?php echo e($request->refNumber); ?></strong>
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
                                                    <strong><?php echo e($request->member->firstName); ?> <?php echo e($request->member->lastName); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo e($request->member->phoneNumber); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo e(number_format($request->requestAmount, 2, ',', ' ')); ?> USD</td>
                                        <td>
                                            <?php
                                                $statusClasses = [
                                                    'draft' => 'bg-secondary',
                                                    'accepted' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'validated' => 'bg-primary',
                                                    'done' => 'bg-info',
                                                    'toreviewed' => 'bg-warning'
                                                ];
                                                $statusLabels = [
                                                    'draft' => 'Brouillon',
                                                    'accepted' => 'Accepté',
                                                    'rejected' => 'Rejeté',
                                                    'validated' => 'Validé',
                                                    'done' => 'Terminé',
                                                    'toreviewed' => 'À réviser'
                                                ];
                                            ?>
                                            <span class="badge <?php echo e($statusClasses[$request->status] ?? 'bg-secondary'); ?>">
                                                <?php echo e($statusLabels[$request->status] ?? $request->status); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($request->submitDate->format('d/m/Y')); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('receptionniste.loans.show', $request->loanDocId)); ?>" 
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
            
            <!-- Pagination -->
            <?php if($recentRequests->hasPages()): ?>
                <div class="card-footer">
                    <nav aria-label="Pagination des demandes">
                        <?php echo e($recentRequests->links()); ?>

                        
                        <!-- Informations de pagination -->
                        <div class="text-center text-muted">
                            <small>
                                Page <?php echo e($recentRequests->currentPage()); ?> sur <?php echo e($recentRequests->lastPage()); ?> 
                                (<?php echo e($recentRequests->total()); ?> demande(s) au total)
                            </small>
                        </div>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stats-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stats-card.border-primary {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.05);
}

.stats-card.border-success {
    border-color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.05);
}

.stats-card.border-info {
    border-color: #0dcaf0 !important;
    background-color: rgba(13, 202, 240, 0.05);
}

.stats-card.border-warning {
    border-color: #ffc107 !important;
    background-color: rgba(255, 193, 7, 0.05);
}

.stats-icon {
    font-size: 2rem;
    opacity: 0.7;
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/receptionniste/dashboard.blade.php ENDPATH**/ ?>