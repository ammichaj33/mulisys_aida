

<?php $__env->startSection('title', 'Cashflow par période'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-chart-line me-2"></i>Rapport détaillé de trésorerie</h2>
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
                    <form method="GET" action="<?php echo e(route('cashflow.reports.by-period')); ?>" class="row g-3">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" 
                                   value="<?php echo e(request('startDate', $startDate)); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" 
                                   value="<?php echo e(request('endDate', $endDate)); ?>">
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
                                <a href="<?php echo e(route('cashflow.reports.by-period')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton Imprimer PDF -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                <a href="<?php echo e(route('cashflow.reports.by-period', array_merge(request()->all(), ['pdf' => 1]))); ?>" 
                   class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf me-2"></i>Imprimer PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Rapport détaillé -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Rapport détaillé de trésorerie</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°</th>
                                    <th>Date</th>
                                    <th>Motif</th>
                                    <th>Types d'opération</th>
                                    <th>Compte</th>
                                    <th>Mode Paiement</th>
                                    <th>Client</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($transaction->transactionDate->format('d/m/Y')); ?></td>
                                    <td><?php echo e(Str::limit($transaction->description ?? $transaction->category->categoryName, 30)); ?></td>
                                    <td>
                                        <?php if($transaction->transactionType == 'income'): ?>
                                            <span class="badge bg-success">Entrée</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Sortie</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($transaction->account->accountName ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                            $paymentMethods = [
                                                'cash' => 'Espèce',
                                                'bank' => 'Banque',
                                                'mobile_money' => 'Mobile Money',
                                                'check' => 'Chèque'
                                            ];
                                        ?>
                                        <?php echo e($paymentMethods[$transaction->paymentMethod] ?? $transaction->paymentMethod); ?>

                                    </td>
                                    <td>
                                        <?php if($transaction->member): ?>
                                            <?php echo e($transaction->member->firstName); ?> <?php echo e($transaction->member->lastName); ?>

                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <strong><?php echo e(number_format($transaction->amount, 2, ',', ' ')); ?> USD</strong>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucune transaction pour cette période</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="7" class="text-end">TOTAL</th>
                                    <th class="text-end"><?php echo e(number_format($totalIncome - $totalExpense, 2, ',', ' ')); ?> USD</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/cashflow/reports/by-period.blade.php ENDPATH**/ ?>