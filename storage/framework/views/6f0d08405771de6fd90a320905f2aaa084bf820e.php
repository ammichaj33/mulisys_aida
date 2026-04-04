

<?php $__env->startSection('title', 'Gestion de la trésorerie'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-wallet me-2"></i>Gestion de la trésorerie</h2>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-cashflow')): ?>
                    <a href="<?php echo e(route('cashflow.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouvelle transaction
                    </a>
                <?php endif; ?>
            </div>
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
                    <form method="GET" action="<?php echo e(route('cashflow.index')); ?>" class="row g-3">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" 
                                   value="<?php echo e(request('startDate')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" 
                                   value="<?php echo e(request('endDate')); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="transactionType" class="form-label">Type</label>
                            <select class="form-select" id="transactionType" name="transactionType">
                                <option value="">Tous</option>
                                <option value="income" <?php echo e(request('transactionType') == 'income' ? 'selected' : ''); ?>>Entrées</option>
                                <option value="expense" <?php echo e(request('transactionType') == 'expense' ? 'selected' : ''); ?>>Sorties</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="categoryId" class="form-label">Catégorie</label>
                            <select class="form-select" id="categoryId" name="categoryId">
                                <option value="">Toutes</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->categoryId); ?>" <?php echo e(request('categoryId') == $category->categoryId ? 'selected' : ''); ?>>
                                        <?php echo e($category->categoryName); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous</option>
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>En attente</option>
                                <option value="confirmed" <?php echo e(request('status') == 'confirmed' ? 'selected' : ''); ?>>Confirmé</option>
                                <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Annulé</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="<?php echo e(request('search')); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                                <a href="<?php echo e(route('cashflow.index')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Totaux -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-down stats-icon"></i>
                    <h3><?php echo e(number_format($totalIncome, 2, ',', ' ')); ?> USD</h3>
                    <p>Total Entrées</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-up stats-icon"></i>
                    <h3><?php echo e(number_format($totalExpense, 2, ',', ' ')); ?> USD</h3>
                    <p>Total Sorties</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-balance-scale stats-icon"></i>
                    <h3><?php echo e(number_format($balance, 2, ',', ' ')); ?> USD</h3>
                    <p>Solde</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Liste des transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Catégorie</th>
                                    <th>Description</th>
                                    <th>Compte</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($transaction->transactionDate->format('d/m/Y')); ?></td>
                                    <td>
                                        <?php if($transaction->transactionType == 'income'): ?>
                                            <span class="badge bg-success">Entrée</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Sortie</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($transaction->category->categoryName); ?></td>
                                    <td><?php echo e(Str::limit($transaction->description, 50)); ?></td>
                                    <td><?php echo e($transaction->account->accountName ?? 'N/A'); ?></td>
                                    <td class="<?php echo e($transaction->transactionType == 'income' ? 'text-success' : 'text-danger'); ?>">
                                        <strong><?php echo e($transaction->transactionType == 'income' ? '+' : '-'); ?><?php echo e(number_format($transaction->amount, 2, ',', ' ')); ?> USD</strong>
                                    </td>
                                    <td>
                                        <?php if($transaction->status == 'confirmed'): ?>
                                            <span class="badge bg-success">Confirmé</span>
                                        <?php elseif($transaction->status == 'pending'): ?>
                                            <span class="badge bg-warning">En attente</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Annulé</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo e(route('cashflow.show', $transaction->cashflowTransactionId)); ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit-cashflow')): ?>
                                                <?php if($transaction->status != 'cancelled'): ?>
                                                    <a href="<?php echo e(route('cashflow.edit', $transaction->cashflowTransactionId)); ?>" 
                                                       class="btn btn-sm btn-outline-warning" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('confirm-cashflow')): ?>
                                                <?php if($transaction->status == 'pending'): ?>
                                                    <form method="POST" action="<?php echo e(route('cashflow.confirm', $transaction->cashflowTransactionId)); ?>" 
                                                          style="display: inline;">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Confirmer">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit-cashflow')): ?>
                                                <?php if($transaction->status != 'cancelled'): ?>
                                                    <form method="POST" action="<?php echo e(route('cashflow.cancel', $transaction->cashflowTransactionId)); ?>" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette transaction ?');">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Annuler">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete-cashflow')): ?>
                                                <?php if($transaction->status != 'confirmed'): ?>
                                                    <form method="POST" action="<?php echo e(route('cashflow.destroy', $transaction->cashflowTransactionId)); ?>" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?');">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucune transaction trouvée</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3">
                        <?php echo e($transactions->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/cashflow/index.blade.php ENDPATH**/ ?>