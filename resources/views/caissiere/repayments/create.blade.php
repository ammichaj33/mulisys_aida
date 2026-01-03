@extends('layouts.app')

@section('title', 'Enregistrer un remboursement')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-money-bill-wave me-2"></i>Enregistrer un remboursement</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success fs-6">
                    {{ $loans->count() }} crédit(s) validé(s)
                </span>
                <a href="{{ route('caissiere.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
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
            <strong>Remboursements :</strong> Sélectionnez un crédit validé pour enregistrer un remboursement.
        </div>
    </div>
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

@if($selectedLoan)
    <!-- Formulaire de remboursement pour un crédit spécifique -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Enregistrer un remboursement</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('caissiere.repayments.store') }}">
                        @csrf
                        <input type="hidden" name="loanDocIdFk" value="{{ $selectedLoan->loanDocId }}">

                        <!-- Informations du crédit sélectionné -->
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <div class="d-flex align-items-center">
                                @if($selectedLoan->member->photo)
                                    <img src="{{ route('members.photo', $selectedLoan->member->memberId) }}" 
                                         alt="Photo de {{ $selectedLoan->member->firstName }} {{ $selectedLoan->member->lastName }}" 
                                         class="rounded-circle me-3" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <strong>Crédit sélectionné :</strong> 
                                    {{ $selectedLoan->refNumber }} - {{ $selectedLoan->member->firstName }} {{ $selectedLoan->member->lastName }}
                                    ({{ round($selectedLoan->requestAmount, 2) }} USD)
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations financières -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-calculator me-2"></i>État du crédit</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Montant total dû:</strong><br>
                                    <span class="text-primary h6">{{ round($selectedLoan->totalAmountDue, 2) }} USD</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Déjà remboursé:</strong><br>
                                    <span class="text-success h6">{{ round($selectedLoan->totalRepaid, 2) }} USD</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Reste dû:</strong><br>
                                    <span class="text-warning h6">{{ round($selectedLoan->remainingAmount, 2) }} USD</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Pénalités non payées:</strong><br>
                                    <span class="text-danger h6">{{ round($selectedLoan->unpaidPenalties ?? 0, 2) }} USD</span>
                                </div>
                            </div>
                            @if(($selectedLoan->unpaidPenalties ?? 0) > 0)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Total à payer :</strong>
                                            <span class="text-danger h4 fw-bold">{{ round($selectedLoan->totalAmountWithPenalties ?? $selectedLoan->remainingAmount, 2) }} USD</span>
                                            <br>
                                            <small class="text-muted">Inclut le reste dû + pénalités non payées</small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <strong>Montant maximum autorisé :</strong>
                                            <span class="text-primary h4 fw-bold">{{ round($selectedLoan->remainingAmount, 2) }} USD</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Montant <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                       value="{{ old('amount') }}" required 
                                       placeholder="0.00" max="{{ $selectedLoan->remainingAmount }}">
                                <span class="input-group-text">USD</span>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Montant maximum autorisé : <strong>{{ round($selectedLoan->remainingAmount, 2) }} USD</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="repaymentDate" class="form-label">Date de remboursement <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="repaymentDate" name="repaymentDate" 
                                   value="{{ old('repaymentDate', date('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="repaymentTypeIdFk" class="form-label">Type de remboursement <span class="text-danger">*</span></label>
                            <select class="form-select" id="repaymentTypeIdFk" name="repaymentTypeIdFk" required>
                                <option value="">Sélectionner un type</option>
                                @foreach($repaymentTypes as $type)
                                    <option value="{{ $type->repaymentTypeID }}" {{ old('repaymentTypeIdFk') == $type->repaymentTypeID ? 'selected' : '' }}>
                                        {{ $type->repaymentName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (optionnel)</label>
                            <textarea class="form-control" id="description" name="description" rows="2" 
                                      placeholder="Notes sur ce remboursement...">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('caissiere.repayments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Enregistrer le remboursement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Informations sur les remboursements -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Important</h6>
                        <ul class="mb-0 small">
                            <li>Le montant ne peut pas dépasser le reste dû</li>
                            <li>Le remboursement sera enregistré immédiatement</li>
                            <li>Un reçu sera généré automatiquement</li>
                            <li>Cette action est irréversible</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Redirection vers la liste des crédits -->
    <script>
        window.location.href = "{{ route('caissiere.repayments.index') }}";
    </script>
@endif

@push('scripts')
<script>
const maxRepaymentAmount = {{ $selectedLoan ? ($selectedLoan->totalAmountWithPenalties ?? $selectedLoan->remainingAmount) : 0 }};

function formatMoney(amount) {
    return Math.round(amount * 100) / 100;
}

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
        this.setCustomValidity('Le montant ne peut pas dépasser le reste dû');
        this.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = 'Le montant ne peut pas dépasser le reste dû (' + formatMoney(maxRepaymentAmount) + ' USD)';
        errorDiv.style.display = 'block';
        inputGroup.parentNode.appendChild(errorDiv);
        
        submitBtn.disabled = true;
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
        submitBtn.disabled = false;
    }
});
</script>
@endpush

@endsection