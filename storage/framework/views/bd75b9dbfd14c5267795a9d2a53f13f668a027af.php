

<?php $__env->startSection('title', 'Modifier le membre'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-edit me-2"></i>Modifier le membre</h2>
            <a href="<?php echo e(route('members.show', $member->memberId)); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

<?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations du membre</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('members.update', $member->memberId)); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" 
                                   value="<?php echo e(old('firstName', $member->firstName)); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" 
                                   value="<?php echo e(old('lastName', $member->lastName)); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phoneNumber" class="form-label">Numéro de téléphone *</label>
                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" 
                                   value="<?php echo e(old('phoneNumber', $member->phoneNumber)); ?>" required>
                            <small class="form-text text-muted">Numéro de téléphone principal</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo e(old('email', $member->email)); ?>">
                            <small class="form-text text-muted">Adresse email (optionnelle)</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="institutionFrom" class="form-label">Institution d'origine *</label>
                        <input type="text" class="form-control" id="institutionFrom" name="institutionFrom" 
                               value="<?php echo e(old('institutionFrom', $member->institutionFrom)); ?>" 
                               placeholder="Ex: Université, École, Organisation..." required>
                        <small class="form-text text-muted">Institution ou organisation d'origine du membre</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birthDate" class="form-label">Date de naissance *</label>
                            <input type="date" class="form-control" id="birthDate" name="birthDate" 
                                   value="<?php echo e(old('birthDate', $member->birthDate->format('Y-m-d'))); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Genre *</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Sélectionner...</option>
                                <option value="M" <?php echo e(old('gender', $member->gender) == 'M' ? 'selected' : ''); ?>>Masculin</option>
                                <option value="F" <?php echo e(old('gender', $member->gender) == 'F' ? 'selected' : ''); ?>>Féminin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" 
                                  placeholder="Adresse complète du membre..." required><?php echo e(old('address', $member->address)); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" 
                                   accept="image/jpeg,image/jpg,image/png,.jpg,.jpeg,.png,.JPG,.JPEG,.PNG">
                            <?php if($member->photo): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Photo actuelle :</small><br>
                                    <img src="<?php echo e(route('members.photo', $member->memberId)); ?>" alt="Photo actuelle" 
                                         class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                                </div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Format: JPG, JPEG, PNG (max 1MB)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="idCard" class="form-label">Scan carte d'identité</label>
                            <input type="file" class="form-control" id="idCard" name="idCard" 
                                   accept=".pdf,image/jpeg,image/jpg,image/png,.jpg,.jpeg,.png,.JPG,.JPEG,.PNG,.PDF">
                            <?php if($member->idCard): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Carte d'identité actuelle :</small><br>
                                    <a href="<?php echo e(route('members.idcard', $member->memberId)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Voir le document
                                    </a>
                                </div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Format: PDF, JPG, JPEG, PNG (max 1MB)</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo e(route('members.show', $member->memberId)); ?>" class="btn btn-secondary me-md-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Modification</h6>
                    <p class="mb-0">Vous pouvez modifier toutes les informations du membre. Les changements seront appliqués immédiatement.</p>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention</h6>
                    <p class="mb-0">Vérifiez que le numéro de téléphone et l'email ne sont pas déjà utilisés par un autre membre.</p>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-calendar me-2"></i>Membre depuis:</label>
                    <span><?php echo e($member->createdAt->format('d/m/Y à H:i')); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item {
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.info-item label {
    font-weight: 600;
    color: #6c757d;
    display: block;
    margin-bottom: 0.25rem;
}

.info-item span {
    color: #495057;
}
</style>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/members/edit.blade.php ENDPATH**/ ?>