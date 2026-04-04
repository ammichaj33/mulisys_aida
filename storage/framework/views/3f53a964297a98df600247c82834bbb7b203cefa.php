<!-- Commentaires de validation -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Commentaires de validation</h5>
            </div>
            <div class="card-body">
                <?php
                    $statusLabels = [
                        'accepted' => 'Accepté',
                        'rejected' => 'Rejeté',
                        'toreviewed' => 'À réviser',
                        'validated' => 'Validé',
                    ];
                    $statusClasses = [
                        'accepted' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'toreviewed' => 'bg-warning text-dark',
                        'validated' => 'bg-primary',
                    ];
                ?>
                <?php $__currentLoopData = $loan->validationHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $timestamp = $history->trackedDate ?? $history->created_at;
                        $description = $history->operDescription ?? '';
                        $comment = null;
                        if (strpos($description, 'Commentaires:') !== false) {
                            $comment = trim(substr($description, strpos($description, 'Commentaires:') + strlen('Commentaires:')));
                        } elseif (strpos($description, 'Validation finale par le gérant:') !== false) {
                            $comment = trim(substr($description, strlen('Validation finale par le gérant:')));
                        }
                        if ($comment === '') {
                            $comment = null;
                        }
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo e($history->user->fullName ?? $history->user->username); ?></strong>
                                <?php if(!empty($history->user->role)): ?>
                                    <span class="badge bg-light text-dark text-uppercase ms-2"><?php echo e(str_replace('_', ' ', $history->user->role)); ?></span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><?php echo e($timestamp ? $timestamp->format('d/m/Y H:i') : ''); ?></small>
                        </div>
                        <div class="mt-2">
                            <span class="badge <?php echo e($statusClasses[$history->recordStatus] ?? 'bg-secondary'); ?>">
                                <?php echo e($statusLabels[$history->recordStatus] ?? ucfirst($history->recordStatus)); ?>

                            </span>
                        </div>
                        <p class="mt-2 mb-0 <?php echo e($comment ? '' : 'text-muted fst-italic'); ?>">
                            <?php echo e($comment ?? 'Aucun commentaire renseigné.'); ?>

                        </p>
                    </div>
                    <?php if(!$loop->last): ?>
                        <hr>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>

<?php /**PATH C:\laragon\www\mulisys_aida\resources\views/loans/_validation-comments.blade.php ENDPATH**/ ?>