@extends('layouts.app')

@section('title', 'Modifier le remboursement')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Modifier le remboursement</h2>
            <a href="{{ route('repayments.show', $repayment->loanRepaymentId) }}" class="btn btn-outline-secondary">
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
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Informations du remboursement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('repayments.update', $repayment->loanRepaymentId) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="loanDocIdFk" class="form-label">Crédit *</label>
                            <select class="form-select" id="loanDocIdFk" name="loanDocIdFk" required>
                                <option value="">Sélectionner un crédit...</option>
                                @foreach($loans as $loan)
                                    <option value="{{ $loan->loanDocId }}" {{ (old('loanDocIdFk', $repayment->loanDocIdFk) == $loan->loanDocId) ? 'selected' : '' }}>
                                        {{ $loan->refNumber }} - {{ $loan->member->firstName }} {{ $loan->member->lastName }} ({{ number_format($loan->requestAmount, 2, ',', ' ') }} USD)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="repaymentTypeIdFk" class="form-label">Type de remboursement *</label>
                            <select class="form-select" id="repaymentTypeIdFk" name="repaymentTypeIdFk" required>
                                <option value="">Sélectionner un type...</option>
                                @foreach($repaymentTypes as $type)
                                    <option value="{{ $type->repaymentTypeID }}" {{ (old('repaymentTypeIdFk', $repayment->repaymentTypeIdFk) == $type->repaymentTypeID) ? 'selected' : '' }}>
                                        {{ $type->repaymentName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Montant *</label>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   value="{{ old('amount', $repayment->amount) }}" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="repaymentDate" class="form-label">Date de remboursement *</label>
                            <input type="date" class="form-control" id="repaymentDate" name="repaymentDate" 
                                   value="{{ old('repaymentDate', $repayment->repaymentDate->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Description du remboursement...">{{ old('description', $repayment->description) }}</textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('repayments.show', $repayment->loanRepaymentId) }}" class="btn btn-secondary me-md-2">Annuler</a>
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
                    <p class="mb-0">Vous pouvez modifier toutes les informations du remboursement. Les changements seront appliqués immédiatement.</p>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention</h6>
                    <p class="mb-0">Vérifiez que le montant et la date sont corrects avant de sauvegarder.</p>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-calendar me-2"></i>Créé le:</label>
                    <span>{{ $repayment->createdAt->format('d/m/Y à H:i') }}</span>
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
@endsection


