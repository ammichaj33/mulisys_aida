

<?php $__env->startSection('title', 'Nouvelle demande de crédit'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle me-2"></i>Nouvelle demande de crédit</h2>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('receptionniste.dashboard')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
                <a href="<?php echo e(route('members.create')); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau membre
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
            <strong>Instructions :</strong> Remplissez tous les champs obligatoires pour créer une nouvelle demande de crédit. 
            Le montant maximum autorisé est de 1 000 000 USD.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informations de la demande</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('receptionniste.loans.store')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="memberSearch" class="form-label">Rechercher un membre *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="memberSearch" 
                                       placeholder="Tapez le nom, prénom, téléphone ou email..." 
                                       autocomplete="off" required>
                                <input type="hidden" id="memberId" name="memberId" required>
                            </div>
                            <div id="memberResults" class="list-group position-absolute" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto; width: 100%;"></div>
                            <?php $__errorArgs = ['memberId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="requestAmount" class="form-label">Montant demandé (USD) *</label>
                            <input type="text" class="form-control money-input" id="requestAmount" 
                                   placeholder="Ex: 500 000" required>
                            <input type="hidden" id="requestAmountValue" name="requestAmount" value="">
                            <small class="form-text text-muted">Montant maximum: 1 000 000 USD</small>
                            <?php $__errorArgs = ['requestAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="loanMonths" class="form-label">Durée (mois) *</label>
                            <input type="number" class="form-control" id="loanMonths" name="loanMonths" 
                                   min="1" max="36" placeholder="Ex: 12" required>
                            <small class="form-text text-muted">Durée en mois (1 à 36 mois)</small>
                            <?php $__errorArgs = ['loanMonths'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="submitDate" class="form-label">Date d'octroi du crédit *</label>
                            <input type="date" class="form-control" id="submitDate" name="submitDate" 
                                   value="<?php echo e(date('Y-m-d')); ?>" required>
                            <small class="form-text text-muted">Date prévue pour l'octroi du crédit</small>
                            <?php $__errorArgs = ['submitDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="document" class="form-label">Document de garantie *</label>
                            <input type="file" class="form-control" id="document" name="document" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            <small class="form-text text-muted">Formats acceptés: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 5MB)</small>
                            <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3" placeholder="Description de la demande..." required></textarea>
                            <small class="form-text text-muted">Décrivez l'objectif du crédit et les garanties</small>
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo e(route('receptionniste.dashboard')); ?>" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer la demande
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
                    <h6><i class="fas fa-lightbulb me-2"></i>Conseils</h6>
                    <ul class="mb-0 small">
                        <li>Vérifiez les informations du membre avant de valider</li>
                        <li>Le montant maximum autorisé est de 1 000 000 USD</li>
                        <li>La durée du crédit peut aller de 1 à 36 mois</li>
                        <li>Un document de garantie est obligatoire</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Important</h6>
                    <p class="mb-0 small">
                        Une fois enregistrée, la demande sera soumise pour validation 
                        par le chargé des crédits.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
// Recherche de membres
const memberSearch = document.getElementById('memberSearch');
const memberResults = document.getElementById('memberResults');
const memberId = document.getElementById('memberId');

memberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    
    if (query.length < 2) {
        memberResults.style.display = 'none';
        return;
    }
    
    searchMembers(query);
});

function searchMembers(query) {
    fetch(`/receptionniste/members/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displayResults(data);
        })
        .catch(error => {
            console.error('Erreur lors de la recherche:', error);
        });
}

function displayResults(members) {
    memberResults.innerHTML = '';
    
    if (members.length === 0) {
        memberResults.innerHTML = '<div class="list-group-item text-muted">Aucun membre trouvé</div>';
    } else {
        members.forEach(member => {
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-action';
            item.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${member.firstName} ${member.lastName}</h6>
                </div>
                <p class="mb-1">${member.phoneNumber}</p>
                ${member.email ? `<small>${member.email}</small>` : ''}
            `;
            
            item.addEventListener('click', function() {
                selectMember(member);
            });
            
            memberResults.appendChild(item);
        });
    }
    
    memberResults.style.display = 'block';
}

function selectMember(member) {
    memberSearch.value = `${member.firstName} ${member.lastName} - ${member.phoneNumber}`;
    memberId.value = member.memberId;
    memberResults.style.display = 'none';
}

// Masquer les résultats quand on clique ailleurs
document.addEventListener('click', function(e) {
    if (!memberSearch.contains(e.target) && !memberResults.contains(e.target)) {
        memberResults.style.display = 'none';
    }
});

// Formatage du montant
document.getElementById('requestAmount').addEventListener('input', function() {
    formatMoney(this);
});

function formatMoney(input) {
    let value = input.value.replace(/\s/g, '');
    
    // Remplacer les virgules par des points pour le calcul
    let numericValue = value.replace(',', '.');
    
    if (numericValue && !isNaN(numericValue)) {
        // Convertir en nombre pour le formatage
        let number = parseFloat(numericValue);
        
        // Vérifier la limite maximale
        if (number > 999999999999) {
            number = 999999999999;
        }
        
        // Mettre à jour le champ caché avec la valeur numérique
        document.getElementById('requestAmountValue').value = number;
        
        // Formater avec espaces (sans décimales pour les montants entiers)
        let formatted = Math.floor(number).toLocaleString('fr-FR');
        
        if (input.value !== formatted) {
            input.value = formatted;
        }
    } else {
        // Si pas de valeur valide, vider le champ caché
        document.getElementById('requestAmountValue').value = '';
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/receptionniste/create.blade.php ENDPATH**/ ?>