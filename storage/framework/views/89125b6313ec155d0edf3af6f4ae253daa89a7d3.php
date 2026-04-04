

<?php $__env->startSection('title', 'Détails de la demande'); ?>

<?php $__env->startSection('content'); ?>
<?php
    // Déterminer la route de retour selon les permissions
    $backRoute = route('loans.index');
    if (auth()->user()->can('view-dashboard-receptionniste')) {
        $backRoute = route('receptionniste.dashboard');
    } elseif (auth()->user()->can('view-dashboard-gerant')) {
        $backRoute = route('gerant.dashboard');
    } elseif (auth()->user()->can('view-dashboard-caissiere')) {
        $backRoute = route('caissiere.dashboard');
    } elseif (auth()->user()->can('view-dashboard-charge-credits')) {
        $backRoute = route('charge_credits.dashboard');
    } elseif (auth()->user()->can('view-dashboard-directeur')) {
        $backRoute = route('directeur.dashboard');
    }
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt me-2"></i>Détails de la demande</h2>
            <div>
                <a href="<?php echo e($backRoute); ?>" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                    <?php if(auth()->user()->can('view-dashboard-gerant') || auth()->user()->can('view-dashboard-caissiere')): ?>
                        au dashboard
                    <?php endif; ?>
                </a>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit-loan-requests')): ?>
                    <?php if($loan->status == 'draft' || $loan->status == 'toreviewed'): ?>
                        <?php if(auth()->user()->can('view-dashboard-receptionniste')): ?>
                            <a href="<?php echo e(route('receptionniste.loans.edit', $loan->loanDocId)); ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Modifier
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-repayments')): ?>
                    <?php if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere')): ?>
                        <a href="<?php echo e(route('caissiere.repayments.create', ['loan_id' => $loan->loanDocId])); ?>" class="btn btn-primary">
                            <i class="fas fa-money-bill-wave me-2"></i>Enregistrer remboursement
                        </a>
                        <?php if($remainingAmount > 0): ?>
                        <a href="<?php echo e(route('caissiere.loans.early-repayment', $loan->loanDocId)); ?>" class="btn btn-warning">
                            <i class="fas fa-clock me-2"></i>Remboursement anticipé
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('validate-credits')): ?>
                    <?php if($loan->status == 'accepted' && auth()->user()->can('view-dashboard-charge-credits')): ?>
                        <a href="<?php echo e(route('charge_credits.loans.validate', $loan->loanDocId)); ?>" class="btn btn-primary">
                            <i class="fas fa-check-circle me-2"></i>Valider
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Informations de la demande -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations de la demande</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Référence:</strong>
                        <p class="text-primary"><?php echo e($loan->refNumber); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Statut:</strong>
                        <p>
                            <?php
                                $statusClass = '';
                                switch ($loan->status) {
                                    case 'draft': $statusClass = 'bg-secondary'; break;
                                    case 'accepted': $statusClass = 'bg-success'; break;
                                    case 'rejected': $statusClass = 'bg-danger'; break;
                                    case 'validated': $statusClass = 'bg-primary'; break;
                                    case 'done': $statusClass = 'bg-info'; break;
                                    case 'toreviewed': $statusClass = 'bg-warning'; break;
                                }
                            ?>
                            <span class="badge <?php echo e($statusClass); ?>">
                                <?php echo e(['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser', 'finalized' => 'Finalisé'][$loan->status]); ?>

                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Date de soumission:</strong>
                        <p><?php echo e($loan->submitDate->format('d/m/Y')); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Durée:</strong>
                        <p><?php echo e($loan->loanMonths); ?> mois</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Montant demandé:</strong>
                        <p class="text-success h5"><?php echo e($calculationService->formatMoney($loan->requestAmount)); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Taux d'intérêt:</strong>
                        <p><?php echo e($loan->interestRate); ?>%</p>
                    </div>
                </div>
                
                <?php if($loan->description): ?>
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p><?php echo e(nl2br(e($loan->description))); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if($loan->docPath): ?>
                <div class="mb-3">
                    <strong>Document:</strong>
                    <p>
                        <a href="<?php echo e(route('loans.document', $loan->loanDocId)); ?>" 
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Télécharger le document
                        </a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Calculs financiers -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Calculs financiers</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Montant du crédit:</span>
                            <span><?php echo e($calculationService->formatMoney($loan->requestAmount)); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Intérêts (<?php echo e($loan->interestRate); ?>% sur <?php echo e($loan->loanMonths); ?> mois):</span>
                            <span><?php echo e($calculationService->formatMoney($interestCalculation['total_interest'])); ?></span>
                        </div>
                        <?php if($totalCancelledInterest > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Intérêts annulés:</span>
                            <span class="text-success">-<?php echo e($calculationService->formatMoney($totalCancelledInterest)); ?></span>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Montant total à rembourser:</strong>
                            <strong class="text-primary"><?php echo e($calculationService->formatMoney($totalAmountDue)); ?></strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Calcul des intérêts dégressifs</h6>
                            <p class="mb-0">
                                Intérêts calculés sur le capital restant à chaque échéance 
                                au taux de <?php echo e($loan->interestRate); ?>% par mois.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <div class="col-lg-4">
        <!-- Informations du membre -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations du membre</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if($loan->member->photo): ?>
                        <img src="<?php echo e(route('members.photo', $loan->member->memberId)); ?>" 
                             alt="Photo de <?php echo e($loan->member->firstName); ?> <?php echo e($loan->member->lastName); ?>" 
                             class="rounded-circle mb-2" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light rounded-circle mb-2 d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-2x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <h6 class="mb-0"><?php echo e($loan->member->firstName); ?> <?php echo e($loan->member->lastName); ?></h6>
                </div>
                
                <div class="mb-3">
                    <strong>Téléphone:</strong>
                    <p>
                        <a href="tel:<?php echo e($loan->member->phoneNumber); ?>" 
                           class="text-decoration-none">
                            <?php echo e($loan->member->phoneNumber); ?>

                        </a>
                    </p>
                </div>
                
                <?php if($loan->member->email): ?>
                <div class="mb-3">
                    <strong>Email:</strong>
                    <p>
                        <a href="mailto:<?php echo e($loan->member->email); ?>" 
                           class="text-decoration-none">
                            <?php echo e($loan->member->email); ?>

                        </a>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if($loan->member->address): ?>
                <div class="mb-3">
                    <strong>Adresse:</strong>
                    <p><?php echo e($loan->member->address); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Date de naissance:</strong>
                    <p><?php echo e($loan->member->birthDate->format('d/m/Y')); ?></p>
                </div>
                
                <div class="mb-3">
                    <strong>Genre:</strong>
                    <p><?php echo e($loan->member->gender == 'M' ? 'Masculin' : 'Féminin'); ?></p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
            </div>
            <div class="card-body">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('generate-repayment-pdf')): ?>
                <div class="d-grid gap-2 mb-3">
                    <a href="<?php echo e(route('loans.schedule.pdf', $loan->loanDocId)); ?>" 
                       class="btn btn-success" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Générer l'échéancier PDF
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit-loan-requests')): ?>
                    <?php if($loan->status == 'draft' || $loan->status == 'toreviewed'): ?>
                        <?php if(auth()->user()->can('view-dashboard-receptionniste')): ?>
                            <div class="d-grid gap-2">
                                <a href="<?php echo e(route('receptionniste.loans.edit', $loan->loanDocId)); ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Modifier la demande
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette demande a été traitée et ne peut plus être modifiée.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-repayments')): ?>
                    <?php if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere')): ?>
                        <div class="d-grid gap-2">
                            <a href="<?php echo e(route('caissiere.repayments.create', ['loan_id' => $loan->loanDocId])); ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-money-bill-wave me-2"></i>Enregistrer remboursement
                            </a>
                            <?php if($remainingAmount > 0): ?>
                            <a href="<?php echo e(route('caissiere.loans.early-repayment', $loan->loanDocId)); ?>" 
                               class="btn btn-warning">
                                <i class="fas fa-clock me-2"></i>Remboursement anticipé
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('caissiere.loans.schedule', $loan->loanDocId)); ?>" 
                               class="btn btn-outline-info">
                                <i class="fas fa-calendar-alt me-2"></i>Calendrier de remboursement
                            </a>
                        </div>
                    <?php elseif($loan->status != 'validated' && auth()->user()->can('view-dashboard-caissiere')): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette demande n'est pas encore validée pour le remboursement.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('final-validate-credits')): ?>
                    <?php if($loan->status == 'accepted' && auth()->user()->can('view-dashboard-gerant')): ?>
                        <div class="d-grid gap-2">
                            <a href="<?php echo e(route('gerant.loans.edit', $loan->loanDocId)); ?>" 
                               class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Modifier dossier
                            </a>
                            <a href="<?php echo e(route('gerant.final-validation')); ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-gavel me-2"></i>Validation finale
                            </a>
                        </div>
                    <?php elseif($loan->status == 'rejected' && auth()->user()->can('view-dashboard-gerant')): ?>
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-ban me-2"></i>
                            Cette demande a été rejetée et ne peut plus être modifiée.
                        </div>
                    <?php elseif(($loan->status == 'validated' || $loan->status == 'done') && auth()->user()->can('view-dashboard-gerant')): ?>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            Cette demande a été validée. Vous pouvez modifier le montant du remboursement si nécessaire.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('validate-credits')): ?>
                    <?php if($loan->status == 'accepted' && auth()->user()->can('view-dashboard-charge-credits')): ?>
                        <div class="d-grid gap-2">
                            <a href="<?php echo e(route('charge_credits.loans.validate', $loan->loanDocId)); ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-check-circle me-2"></i>Valider
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Historique des remboursements (avant le calendrier) -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete-repayments')): ?>
    <?php if(auth()->user()->can('view-dashboard-gerant') && $loan->loanRepayments->count() > 0): ?>
        <?php echo $__env->make('loans._repayment-history-gerant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-repayments')): ?>
    <?php if(!auth()->user()->can('view-dashboard-gerant') || !auth()->user()->can('delete-repayments')): ?>
        <?php echo $__env->make('loans._repayment-history', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
<?php endif; ?>

<!-- Calendrier de remboursement détaillé -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-repayment-schedule')): ?>
<?php echo $__env->make('loans._repayment-schedule', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>

<!-- Commentaires de validation (après le calendrier) -->
<?php if($loan->validationHistory->isNotEmpty()): ?>
<?php echo $__env->make('loans._validation-comments', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-repayments')): ?>
    <?php if(auth()->user()->can('view-dashboard-caissiere')): ?>
        <?php $__env->startPush('scripts'); ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script>
        function printRepaymentReceipt(repaymentId, repaymentType, amount, date, recordedBy) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [80, 200]
            });
            
            doc.setFont('helvetica');
            doc.setFontSize(8);
            
            let y = 10;
            const lineHeight = 4;
            const pageWidth = 80;
            
            doc.setFontSize(10);
            doc.setFont('helvetica', 'bold');
            doc.text('MICROCREDIT SYSTEM', pageWidth/2, y, { align: 'center' });
            y += lineHeight + 2;
            
            doc.setFontSize(8);
            doc.setFont('helvetica', 'bold');
            doc.text('REÇU DE REMBOURSEMENT', pageWidth/2, y, { align: 'center' });
            y += lineHeight + 2;
            
            doc.setFont('helvetica', 'normal');
            doc.text(`Date: ${new Date().toLocaleDateString('fr-FR')}`, pageWidth/2, y, { align: 'center' });
            y += lineHeight;
            doc.text(`Heure: ${new Date().toLocaleTimeString('fr-FR')}`, pageWidth/2, y, { align: 'center' });
            y += lineHeight + 3;
            
            doc.line(5, y, pageWidth-5, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('N° Dossier:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text('<?php echo e($loan->refNumber); ?>', 25, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Membre:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text('<?php echo e($loan->member->firstName); ?> <?php echo e($loan->member->lastName); ?>', 20, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Téléphone:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text('<?php echo e($loan->member->phoneNumber); ?>', 25, y);
            y += lineHeight + 2;
            
            doc.line(5, y, pageWidth-5, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Type:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text(repaymentType, 20, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Montant:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text(amount, 25, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Date remb.:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text(date, 30, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Enregistré par:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text(recordedBy, 35, y);
            y += lineHeight + 2;
            
            doc.line(5, y, pageWidth-5, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('STATUT: <?php echo e(['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser', 'finalized' => 'Finalisé'][$loan->status]); ?>', pageWidth/2, y, { align: 'center' });
            y += lineHeight + 3;
            
            doc.line(5, y, pageWidth-5, y);
            y += lineHeight;
            
            doc.setFontSize(6);
            doc.text('Merci pour votre confiance', pageWidth/2, y, { align: 'center' });
            y += lineHeight;
            doc.text('www.microcredit-system.com', pageWidth/2, y, { align: 'center' });
            
            doc.save(`recu_remboursement_${repaymentId}_${new Date().getTime()}.pdf`);
        }
        </script>
        <?php $__env->stopPush(); ?>
    <?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/loans/show.blade.php ENDPATH**/ ?>