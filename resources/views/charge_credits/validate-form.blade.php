@extends('layouts.app')

@section('title', 'Valider une demande')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-check-circle me-2"></i>Valider une demande</h2>
            <a href="{{ route('charge_credits.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
            </a>
        </div>
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

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
                        <p class="text-primary">{{ $loan->refNumber }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Statut actuel:</strong>
                        <p><span class="badge bg-success">Brouillon</span></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Date de soumission:</strong>
                        <p>{{ $loan->submitDate->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Durée:</strong>
                        <p>{{ $loan->loanMonths }} mois</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Montant demandé:</strong>
                        <p class="text-success h5">{{ $calculationService->formatMoney($loan->requestAmount) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Taux d'intérêt:</strong>
                        <p>{{ $loan->interestRate }}%</p>
                    </div>
                </div>
                
                @if($loan->description)
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p>{{ nl2br(e($loan->description)) }}</p>
                </div>
                @endif
                
                @if($loan->docPath)
                <div class="mb-3">
                    <strong>Document:</strong>
                    <p>
                        <a href="{{ route('loans.document', $loan->loanDocId) }}" 
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Télécharger le document
                        </a>
                    </p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Calculs financiers -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Calculs financiers</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Montant du crédit:</span>
                            <span>{{ $calculationService->formatMoney($loan->requestAmount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Intérêts ({{ $loan->interestRate }}% sur {{ $loan->loanMonths }} mois):</span>
                            <span>{{ $calculationService->formatMoney($interestCalculation['total_interest']) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Montant total à rembourser:</strong>
                            <strong class="text-primary">{{ $calculationService->formatMoney($interestCalculation['total_amount']) }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Calcul des intérêts dégressifs</h6>
                            <p class="mb-0">
                                Intérêts calculés sur le capital restant à chaque échéance 
                                au taux de {{ $loan->interestRate }}% par mois.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulaire de validation -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-gavel me-2"></i>Décision</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('charge_credits.loans.validate', $loan->loanDocId) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="comments" class="form-label">Commentaires *</label>
                        <textarea class="form-control" id="comments" name="comments" rows="4" 
                                  placeholder="Expliquez votre décision..." required></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('charge_credits.dashboard') }}" class="btn btn-secondary me-md-2">Annuler</a>
                        <button type="submit" name="decision" value="rejected" class="btn btn-danger me-md-2"
                                onclick="return confirm('Êtes-vous sûr de vouloir rejeter cette demande ?')">
                            <i class="fas fa-times me-2"></i>Rejeter
                        </button>
                        <button type="submit" name="decision" value="accepted" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Accepter
                        </button>
                    </div>
                </form>
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
                    @if($loan->member->photo)
                        <img src="{{ route('members.photo', $loan->member->memberId) }}" 
                             alt="Photo de {{ $loan->member->firstName }} {{ $loan->member->lastName }}" 
                             class="rounded-circle mb-2" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                        <div class="bg-light rounded-circle mb-2 d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-2x text-muted"></i>
                        </div>
                    @endif
                    <h6 class="mb-0">{{ $loan->member->firstName }} {{ $loan->member->lastName }}</h6>
                </div>
                
                <div class="mb-3">
                    <strong>Téléphone:</strong>
                    <p>
                        <a href="tel:{{ $loan->member->phoneNumber }}" 
                           class="text-decoration-none">
                            {{ $loan->member->phoneNumber }}
                        </a>
                    </p>
                </div>
                
                @if($loan->member->email)
                <div class="mb-3">
                    <strong>Email:</strong>
                    <p>
                        <a href="mailto:{{ $loan->member->email }}" 
                           class="text-decoration-none">
                            {{ $loan->member->email }}
                        </a>
                    </p>
                </div>
                @endif
                
                <div class="mb-3">
                    <strong>Date de naissance:</strong>
                    <p>{{ $loan->member->birthDate->format('d/m/Y') }}</p>
                </div>
                
                <div class="mb-3">
                    <strong>Genre:</strong>
                    <p>{{ $loan->member->gender == 'M' ? 'Masculin' : 'Féminin' }}</p>
                </div>
                
                @if($loan->member->idCard)
                <div class="mb-3">
                    <strong>Carte d'identité:</strong>
                    <p>
                        <a href="{{ route('members.idcard', $loan->member->memberId) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Voir le document
                        </a>
                    </p>
                </div>
                @endif
                
                @if($loan->member->address)
                <div class="mb-3">
                    <strong>Adresse:</strong>
                    <p>{{ $loan->member->address }}</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="tel:{{ $loan->member->phoneNumber }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-phone me-2"></i>Appeler le membre
                    </a>
                    @if($loan->member->email)
                    <a href="mailto:{{ $loan->member->email }}" 
                       class="btn btn-outline-info">
                        <i class="fas fa-envelope me-2"></i>Envoyer un email
                    </a>
                    @endif
                    <a href="{{ route('charge_credits.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
