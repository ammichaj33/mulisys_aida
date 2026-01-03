@extends('layouts.app')

@section('title', 'Modifier le dossier')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Modifier le dossier</h2>
            <div>
                <a href="{{ route('gerant.loans.show', $loan->loanDocId) }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Retour au dossier
                </a>
                <a href="{{ route('gerant.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Messages informatifs permanents -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Modification du dossier :</strong> Vous pouvez ajuster le montant, la durée et le taux d'intérêt du crédit accepté.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Informations du membre (lecture seule) -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations du membre</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            @if($loan->member->photo)
                                <img src="{{ route('members.photo', $loan->member->memberId) }}" 
                                     alt="Photo de {{ $loan->member->firstName }} {{ $loan->member->lastName }}" 
                                     class="rounded-circle me-3" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-user fa-2x text-muted"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $loan->member->firstName }} {{ $loan->member->lastName }}</h6>
                                <small class="text-muted">{{ $loan->member->phoneNumber }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Référence :</strong> {{ $loan->refNumber }}</p>
                        <p><strong>Statut :</strong> <span class="badge bg-info">Accepté</span></p>
                        <p><strong>Date de soumission :</strong> {{ $loan->submitDate->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier les conditions du crédit</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('gerant.loans.update', $loan->loanDocId) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="requestAmount" class="form-label">
                                Montant demandé <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control @error('requestAmount') is-invalid @enderror" 
                                       id="requestAmount" name="requestAmount" 
                                       value="{{ old('requestAmount', $loan->requestAmount) }}" 
                                       min="1" max="1000000" required>
                                <span class="input-group-text">USD</span>
                            </div>
                            @error('requestAmount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Montant maximum autorisé : 1 000 000 USD
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="loanMonths" class="form-label">
                                Durée du crédit <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('loanMonths') is-invalid @enderror" 
                                       id="loanMonths" name="loanMonths" 
                                       value="{{ old('loanMonths', $loan->loanMonths) }}" 
                                       min="1" max="36" required>
                                <span class="input-group-text">mois</span>
                            </div>
                            @error('loanMonths')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Durée entre 1 et 36 mois
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="interestRate" class="form-label">
                                Taux d'intérêt <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control @error('interestRate') is-invalid @enderror" 
                                       id="interestRate" name="interestRate" 
                                       value="{{ old('interestRate', $loan->interestRate) }}" 
                                       min="0" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                            @error('interestRate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Taux entre 0% et 100%
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-calculator me-2"></i>Calcul automatique</h6>
                                <p class="mb-0">
                                    Les intérêts seront calculés automatiquement selon le taux dégressif 
                                    sur le capital restant à chaque échéance.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('gerant.loans.show', $loan->loanDocId) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Informations actuelles -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Conditions actuelles</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Montant :</strong>
                    <p class="text-primary h5">{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</p>
                </div>
                
                <div class="mb-3">
                    <strong>Durée :</strong>
                    <p>{{ $loan->loanMonths }} mois</p>
                </div>
                
                <div class="mb-3">
                    <strong>Taux d'intérêt :</strong>
                    <p>{{ $loan->interestRate }}% par mois</p>
                </div>
                
                @if($loan->description)
                <div class="mb-3">
                    <strong>Description :</strong>
                    <p class="text-muted">{{ $loan->description }}</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Avertissement -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Important</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <ul class="mb-0 small">
                        <li>Seuls le montant, la durée et le taux peuvent être modifiés</li>
                        <li>Les modifications seront enregistrées dans l'historique</li>
                        <li>Le calendrier de remboursement sera recalculé automatiquement</li>
                        <li>Cette action est irréversible</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


