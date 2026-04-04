

<?php $__env->startSection('title', 'Détail du remboursement'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-receipt me-2"></i>Détail du remboursement</h2>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('receipts.repayment', $repayment->loanRepaymentId)); ?>" target="_blank" class="btn btn-success">
                    <i class="fas fa-print me-2"></i>Imprimer reçu
                </a>
                <a href="<?php echo e(route('receipts.repayment.download', $repayment->loanRepaymentId)); ?>" class="btn btn-outline-success">
                    <i class="fas fa-download me-2"></i>Télécharger PDF
                </a>
                <a href="<?php echo e(route('caissiere.repayments.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations du remboursement</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Référence du remboursement</label>
                            <p class="form-control-plaintext"><?php echo e($repayment->loanRepaymentId); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Montant</label>
                            <p class="form-control-plaintext h4 text-success"><?php echo e(round($repayment->amount, 2)); ?> USD</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date de remboursement</label>
                            <p class="form-control-plaintext"><?php echo e(date('d/m/Y', strtotime($repayment->repaymentDate))); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type de remboursement</label>
                            <p class="form-control-plaintext"><?php echo e($repayment->repaymentType->repaymentName); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Enregistré par</label>
                            <p class="form-control-plaintext"><?php echo e($repayment->user->name ?? 'Système'); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date d'enregistrement</label>
                            <p class="form-control-plaintext"><?php echo e(date('d/m/Y H:i', strtotime($repayment->createdAt))); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if($repayment->description): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <p class="form-control-plaintext"><?php echo e($repayment->description); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations du membre</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <?php if($repayment->loanDoc->member->photo): ?>
                        <img src="<?php echo e(route('members.photo', $repayment->loanDoc->member->memberId)); ?>" 
                             alt="Photo de <?php echo e($repayment->loanDoc->member->firstName); ?> <?php echo e($repayment->loanDoc->member->lastName); ?>" 
                             class="rounded-circle me-3" 
                             style="width: 60px; height: 60px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user text-muted fa-2x"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h6 class="mb-0"><?php echo e($repayment->loanDoc->member->firstName); ?> <?php echo e($repayment->loanDoc->member->lastName); ?></h6>
                        <small class="text-muted"><?php echo e($repayment->loanDoc->member->phoneNumber); ?></small>
                    </div>
                </div>
                
                <div class="mb-2">
                    <strong>Adresse:</strong><br>
                    <small class="text-muted"><?php echo e($repayment->loanDoc->member->address); ?></small>
                </div>
                
                <div class="mb-2">
                    <strong>Email:</strong><br>
                    <small class="text-muted"><?php echo e($repayment->loanDoc->member->email ?? 'Non renseigné'); ?></small>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informations du crédit</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Référence:</strong><br>
                    <span class="text-primary"><?php echo e($repayment->loanDoc->refNumber); ?></span>
                </div>
                
                <div class="mb-2">
                    <strong>Montant du crédit:</strong><br>
                    <span class="text-primary"><?php echo e(round($repayment->loanDoc->requestAmount, 2)); ?> USD</span>
                </div>
                
                <div class="mb-2">
                    <strong>Durée:</strong><br>
                    <span class="text-muted"><?php echo e($repayment->loanDoc->loanMonths); ?> mois</span>
                </div>
                
                <div class="mb-2">
                    <strong>Taux d'intérêt:</strong><br>
                    <span class="text-muted"><?php echo e($repayment->loanDoc->interestRate); ?>%</span>
                </div>
                
                <div class="mb-2">
                    <strong>Statut:</strong><br>
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
                        $statusLabel = $statusLabels[$repayment->loanDoc->status] ?? ucfirst($repayment->loanDoc->status);
                        $statusClassValue = $statusClass[$repayment->loanDoc->status] ?? 'bg-secondary';
                    ?>
                    <span class="badge <?php echo e($statusClassValue); ?>"><?php echo e($statusLabel); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/caissiere/repayments/show.blade.php ENDPATH**/ ?>