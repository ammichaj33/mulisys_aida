@extends('layouts.app')

@section('title', 'Modifier le crédit')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Modifier le crédit</h2>
            <a href="{{ route('receptionniste.loans.show', $loan->loanDocId) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informations du crédit</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('receptionniste.loans.update', $loan->loanDocId) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="memberId" class="form-label">Membre *</label>
                            <select class="form-select" id="memberId" name="memberId" required>
                                <option value="">Sélectionner un membre...</option>
                                @foreach(\App\Models\Member::where('isActive', true)->get() as $member)
                                    <option value="{{ $member->memberId }}" {{ (old('memberId', $loan->memberIdFk) == $member->memberId) ? 'selected' : '' }}>
                                        {{ $member->firstName }} {{ $member->lastName }} - {{ $member->phoneNumber }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="requestAmount" class="form-label">Montant demandé (USD) *</label>
                            <input type="text" class="form-control money-input" id="requestAmount" 
                                   value="{{ old('requestAmount', number_format($loan->requestAmount, 0, ',', ' ')) }}" 
                                   placeholder="Ex: 1 000 000" required>
                            <input type="hidden" id="requestAmountValue" name="requestAmount" value="{{ old('requestAmount', $loan->requestAmount) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="loanMonths" class="form-label">Durée (mois) *</label>
                            <input type="number" class="form-control" id="loanMonths" name="loanMonths" 
                                   value="{{ old('loanMonths', $loan->loanMonths) }}" 
                                   min="1" max="36" placeholder="Ex: 12" required>
                            <small class="form-text text-muted">Durée en mois (1 à 36 mois)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="submitDate" class="form-label">Date d'octroi du crédit *</label>
                            <input type="date" class="form-control" id="submitDate" name="submitDate" 
                                   value="{{ old('submitDate', $loan->submitDate->format('Y-m-d')) }}" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="interestRate" class="form-label">Taux d'intérêt (%)</label>
                            <input type="number" class="form-control" id="interestRate" name="interestRate" 
                                   value="{{ old('interestRate', $loan->interestRate) }}" step="0.01" min="0" max="100">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" {{ (old('status', $loan->status) == 'draft') ? 'selected' : '' }}>Brouillon</option>
                                <option value="accepted" {{ (old('status', $loan->status) == 'accepted') ? 'selected' : '' }}>Accepté</option>
                                <option value="rejected" {{ (old('status', $loan->status) == 'rejected') ? 'selected' : '' }}>Rejeté</option>
                                <option value="validated" {{ (old('status', $loan->status) == 'validated') ? 'selected' : '' }}>Validé</option>
                                <option value="done" {{ (old('status', $loan->status) == 'done') ? 'selected' : '' }}>Terminé</option>
                                <option value="toreviewed" {{ (old('status', $loan->status) == 'toreviewed') ? 'selected' : '' }}>À réviser</option>
                                <option value="finalized" {{ (old('status', $loan->status) == 'finalized') ? 'selected' : '' }}>Finalisé</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description de la demande *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Décrivez l'objet du crédit..." required>{{ old('description', $loan->description) }}</textarea>
                        <small class="form-text text-muted">Décrivez l'objectif du crédit et les garanties</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="document" class="form-label">Document de demande</label>
                        <input type="file" class="form-control" id="document" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        @if($loan->docPath)
                            <div class="mt-2">
                                <small class="text-muted">Document actuel : <a href="{{ route('loans.document', $loan->loanDocId) }}" target="_blank">Voir le document</a></small>
                            </div>
                        @endif
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('receptionniste.loans.show', $loan->loanDocId) }}" class="btn btn-secondary me-md-2">Annuler</a>
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
                    <p class="mb-0">Vous pouvez modifier toutes les informations du crédit. Les changements seront appliqués immédiatement.</p>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention</h6>
                    <p class="mb-0">Vérifiez que toutes les informations sont correctes avant de sauvegarder.</p>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-calendar me-2"></i>Créé le:</label>
                    <span>{{ $loan->createdAt->format('d/m/Y à H:i') }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-hashtag me-2"></i>Référence:</label>
                    <span><strong>{{ $loan->refNumber }}</strong></span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Formatage du montant avec espaces
document.getElementById('requestAmount').addEventListener('input', function() {
    formatAmountInput(this);
});

document.querySelector('form').addEventListener('submit', function(e) {
    const amountValue = document.getElementById('requestAmountValue').value;
    if (!amountValue || parseFloat(amountValue) <= 0) {
        e.preventDefault();
        alert('Veuillez saisir un montant valide');
        document.getElementById('requestAmount').focus();
        return false;
    }
});

function formatAmountInput(input) {
    let value = input.value.replace(/[^\d.,]/g, '');
    let numericValue = value.replace(',', '.');
    if (numericValue && !isNaN(numericValue)) {
        let number = parseFloat(numericValue);
        if (number > 999999999999) {
            number = 999999999999;
        }
        document.getElementById('requestAmountValue').value = number;
        let formatted = Math.floor(number).toLocaleString('fr-FR');
        if (input.value !== formatted) {
            input.value = formatted;
        }
    } else {
        document.getElementById('requestAmountValue').value = '';
    }
}
</script>
@endpush

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
@endsection
