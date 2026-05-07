

<?php $__env->startSection('title', 'Liste des crédits pour remboursement'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-money-bill-wave me-2"></i>Liste des crédits pour remboursement</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success fs-6">
                    <?php echo e($loans->count()); ?> crédit(s) validé(s)
                </span>
                <a href="<?php echo e(route('gerant.dashboard')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filtre de recherche -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Rechercher un crédit</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('gerant.repayments.index')); ?>" class="row g-3">
                    <div class="col-md-8">
                        <label for="search" class="form-label">Rechercher par :</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo e(request('search')); ?>" 
                               placeholder="Nom du membre, numéro de téléphone ou référence du dossier...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Rechercher
                            </button>
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(route('gerant.repayments.index')); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Effacer
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if($loans->isEmpty()): ?>
    <div class="text-center py-5">
        <?php if(request('search')): ?>
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aucun résultat trouvé</h5>
            <p class="text-muted">Aucun crédit ne correspond à votre recherche "<?php echo e(request('search')); ?>".</p>
            <a href="<?php echo e(route('gerant.repayments.index')); ?>" class="btn btn-outline-primary">
                <i class="fas fa-list me-2"></i>Voir tous les crédits
            </a>
        <?php else: ?>
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h5 class="text-success">Aucun crédit validé disponible</h5>
            <p class="text-muted">Tous les crédits validés ont été entièrement remboursés.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <?php if(request('search')): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-search me-2"></i>
                    <strong>Résultats de recherche :</strong> <?php echo e($loans->count()); ?> crédit(s) trouvé(s) pour "<?php echo e(request('search')); ?>"
                    <a href="<?php echo e(route('gerant.repayments.index')); ?>" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-times me-1"></i>Effacer la recherche
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php $__currentLoopData = $loans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            <?php echo e($loan->refNumber); ?>

                        </h6>
                        <span class="badge bg-success">Validé</span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center">
                                    <?php if($loan->member->photo): ?>
                                        <img src="<?php echo e(route('members.photo', $loan->member->memberId)); ?>" 
                                             alt="Photo de <?php echo e($loan->member->firstName); ?> <?php echo e($loan->member->lastName); ?>" 
                                             class="rounded-circle me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo e($loan->member->firstName); ?> <?php echo e($loan->member->lastName); ?></strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-phone me-1"></i>
                                            <?php echo e($loan->member->phoneNumber); ?>

                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <strong>Montant:</strong><br>
                                <span class="text-primary fs-5"><?php echo e(round($loan->requestAmount, 2)); ?> USD</span><br>
                                <small class="text-muted"><?php echo e($loan->loanMonths); ?> Mois</small>
                            </div>
                        </div>
                        
                        <?php if($loan->description): ?>
                            <div class="mb-3">
                                <strong>Description:</strong><br>
                                <p class="text-muted"><?php echo e($loan->description); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>Adresse:</strong><br>
                            <small class="text-muted"><?php echo e($loan->member->address); ?></small>
                        </div>
                        
                        <!-- Informations financières -->
                        <div class="alert alert-info mb-3">
                            <h6><i class="fas fa-calculator me-2"></i>État du crédit</h6>
                            <div class="row">
                                <div class="col-4">
                                    <strong>Total dû:</strong><br>
                                    <span class="text-primary"><?php echo e(round($loan->totalAmountDue, 2)); ?> USD</span>
                                </div>
                                <div class="col-4">
                                    <strong>Remboursé:</strong><br>
                                    <span class="text-success"><?php echo e(round($loan->totalRepaid, 2)); ?> USD</span>
                                </div>
                                <div class="col-4">
                                    <strong>Reste dû:</strong><br>
                                    <span class="text-danger fw-bold"><?php echo e(round($loan->remainingAmount, 2)); ?> USD</span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($loan->docPath): ?>
                            <div class="mb-3">
                                <a href="<?php echo e(route('loans.document', $loan->loanDocId)); ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-file me-1"></i>Voir le document
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo e(route('gerant.loans.show', $loan->loanDocId)); ?>" 
                               class="btn btn-outline-primary me-2">
                                <i class="fas fa-eye me-1"></i>Voir détails
                            </a>
                            <?php
                                $repayments = $loan->loanRepayments;
                            ?>
                            <?php if($repayments->count() > 0): ?>
                                <a href="<?php echo e(route('gerant.loans.show', $loan->loanDocId)); ?>#repayments" 
                                   class="btn btn-info me-2">
                                    <i class="fas fa-list me-1"></i>Voir remboursements (<?php echo e($repayments->count()); ?>)
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/gerant/repayments/index.blade.php ENDPATH**/ ?>