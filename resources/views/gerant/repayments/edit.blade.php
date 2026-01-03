@extends('layouts.app')

@section('title', 'Modifier un remboursement')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-edit me-2"></i>Modifier un remboursement
    </h1>
    <a href="{{ route('gerant.repayments.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($repayment->loanDoc->status != 'validated')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Attention :</strong> Les remboursements ne peuvent être modifiés que lorsque le crédit a le statut "Validé". 
        Le statut actuel du crédit est : <strong>{{ ucfirst($repayment->loanDoc->status) }}</strong>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations du remboursement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('gerant.repayments.update', $repayment->loanRepaymentId) }}" 
                      @if($repayment->loanDoc->status != 'validated') onsubmit="event.preventDefault(); alert('Les remboursements ne peuvent être modifiés que lorsque le crédit a le statut \"Validé\".'); return false;" @endif>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Crédit</label>
                        <div class="d-flex align-items-center">
                            @if($repayment->loanDoc->member->photo)
                                <img src="{{ route('members.photo', $repayment->loanDoc->member->memberId) }}" 
                                     alt="Photo de {{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}" 
                                     class="rounded-circle me-3" 
                                     style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-user text-muted"></i>
                                </div>
                            @endif
                            <div>
                                <strong>{{ $repayment->loanDoc->refNumber }} - {{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informations du crédit -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-calculator me-2"></i>Informations du crédit</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Montant total dû:</strong><br>
                                <span id="totalAmount">{{ round($loan->totalAmountDue, 2) }} USD</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Déjà remboursé:</strong><br>
                                <span id="repaidAmount" class="text-success">{{ round($loan->totalRepaid, 2) }} USD</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Reste dû:</strong><br>
                                <span id="remainingAmount" class="text-danger">
                                    <strong>{{ round($loan->remainingAmount, 2) }} USD</strong>
                                </span>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-2 mb-0">
                            <small><i class="fas fa-info-circle me-1"></i>Le montant actuel de ce remboursement ({{ round($repayment->amount, 2) }} USD) n'est pas inclus dans "Déjà remboursé"</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                   value="{{ old('amount', $repayment->amount) }}" required>
                            <span class="input-group-text">USD</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="repaymentDate" class="form-label">Date de remboursement <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="repaymentDate" name="repaymentDate" 
                               value="{{ old('repaymentDate', date('Y-m-d', strtotime($repayment->repaymentDate))) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="repaymentTypeIdFk" class="form-label">Type de remboursement <span class="text-danger">*</span></label>
                        <select class="form-select" id="repaymentTypeIdFk" name="repaymentTypeIdFk" required>
                            @foreach($repaymentTypes as $type)
                                <option value="{{ $type->repaymentTypeID }}" 
                                    {{ old('repaymentTypeIdFk', $repayment->repaymentTypeIdFk) == $type->repaymentTypeID ? 'selected' : '' }}>
                                    {{ $type->repaymentName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Description optionnelle...">{{ old('description', $repayment->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('gerant.repayments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary" @if($repayment->loanDoc->status != 'validated') disabled @endif>
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations</h5>
            </div>
            <div class="card-body">
                <p><strong>Crédit :</strong> {{ $repayment->loanDoc->refNumber }}</p>
                <div class="d-flex align-items-center mb-2">
                    @if($repayment->loanDoc->member->photo)
                        <img src="{{ route('members.photo', $repayment->loanDoc->member->memberId) }}" 
                             alt="Photo de {{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}" 
                             class="rounded-circle me-3" 
                             style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px;">
                            <i class="fas fa-user text-muted"></i>
                        </div>
                    @endif
                    <div>
                        <p class="mb-1"><strong>Membre :</strong> {{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}</p>
                        <p class="mb-0"><strong>Téléphone :</strong> {{ $repayment->loanDoc->member->phoneNumber }}</p>
                    </div>
                </div>
                <p><strong>Montant du crédit :</strong> {{ number_format($repayment->loanDoc->requestAmount, 2, ',', ' ') }} USD</p>
                <hr>
                <p><strong>Date de début :</strong> {{ \Carbon\Carbon::parse($repayment->loanDoc->submitDate)->format('d/m/Y') }}</p>
                <p><strong>Durée du crédit :</strong> {{ $repayment->loanDoc->loanMonths }} mois</p>
                <p><strong>Date d'échéance :</strong> <span class="text-danger"><strong>{{ \Carbon\Carbon::parse($repayment->loanDoc->submitDate)->addMonths($repayment->loanDoc->loanMonths)->format('d/m/Y') }}</strong></span></p>
                <hr>
                <p><strong>Enregistré par :</strong> {{ $repayment->user->name ?? 'N/A' }}</p>
                <p><strong>Date d'enregistrement :</strong> {{ date('d/m/Y H:i', strtotime($repayment->createdAt)) }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const maxRepaymentAmount = {{ $loan->remainingAmount }};

function formatMoney(amount) {
    return Math.round(amount * 100) / 100;
}

// Validation du montant en temps réel
document.getElementById('amount').addEventListener('input', function() {
    const amount = parseFloat(this.value);
    const submitBtn = document.querySelector('button[type="submit"]');
    const inputGroup = this.closest('.input-group');
    
    // Supprimer tous les anciens messages d'erreur pour éviter la duplication
    const existingErrors = inputGroup.parentNode.querySelectorAll('.invalid-feedback');
    existingErrors.forEach(err => err.remove());
    
    if (isNaN(amount) || amount <= 0) {
        this.setCustomValidity('Le montant doit être supérieur à 0');
        this.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = 'Le montant doit être supérieur à 0';
        errorDiv.style.display = 'block';
        inputGroup.parentNode.appendChild(errorDiv);
        
        submitBtn.disabled = true;
    } else if (amount > maxRepaymentAmount) {
        this.setCustomValidity('Le montant doit être inférieur ou égal au reste dû');
        this.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = 'Le montant doit être inférieur ou égal au reste dû (' + formatMoney(maxRepaymentAmount) + ' USD)';
        errorDiv.style.display = 'block';
        inputGroup.parentNode.appendChild(errorDiv);
        
        submitBtn.disabled = true;
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
        submitBtn.disabled = false;
    }
});

// Validation du formulaire avant soumission
document.querySelector('form').addEventListener('submit', function(e) {
    const amount = parseFloat(document.getElementById('amount').value);
    
    if (amount > maxRepaymentAmount) {
        e.preventDefault();
        alert('Le montant du remboursement (' + formatMoney(amount) + ' USD) doit être inférieur ou égal au reste dû (' + formatMoney(maxRepaymentAmount) + ' USD)');
        return false;
    }
    
    if (amount <= 0) {
        e.preventDefault();
        alert('Le montant doit être supérieur à 0');
        return false;
    }
    
    // Confirmation si remboursement total
    if (amount >= maxRepaymentAmount) {
        if (!confirm('Ce remboursement soldera le crédit. Confirmer ?')) {
            e.preventDefault();
            return false;
        }
    }
});

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('amount').max = maxRepaymentAmount;
});
</script>
@endpush
@endsection

