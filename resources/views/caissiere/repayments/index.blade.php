@extends('layouts.app')

@section('title', 'Liste des crédits pour remboursement')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-money-bill-wave me-2"></i>Liste des crédits pour remboursement</h2>
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

<!-- Filtre de recherche -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Rechercher un crédit</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('caissiere.repayments.index') }}" class="row g-3">
                    <div class="col-md-8">
                        <label for="search" class="form-label">Rechercher par :</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nom du membre, numéro de téléphone ou référence du dossier...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Rechercher
                            </button>
                            @if(request('search'))
                                <a href="{{ route('caissiere.repayments.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Effacer
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
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

@if ($loans->isEmpty())
    <div class="text-center py-5">
        @if(request('search'))
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aucun résultat trouvé</h5>
            <p class="text-muted">Aucun crédit ne correspond à votre recherche "{{ request('search') }}".</p>
            <a href="{{ route('caissiere.repayments.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-2"></i>Voir tous les crédits
            </a>
        @else
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h5 class="text-success">Aucun crédit validé disponible</h5>
            <p class="text-muted">Tous les crédits validés ont été entièrement remboursés.</p>
        @endif
    </div>
@else
    <!-- Indicateur de résultats -->
    @if(request('search'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-search me-2"></i>
                    <strong>Résultats de recherche :</strong> {{ $loans->count() }} crédit(s) trouvé(s) pour "{{ request('search') }}"
                    <a href="{{ route('caissiere.repayments.index') }}" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-times me-1"></i>Effacer la recherche
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        @foreach ($loans as $loan)
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            {{ $loan->refNumber }}
                        </h6>
                        <span class="badge bg-success">Validé</span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center">
                                    @if($loan->member->photo)
                                        <img src="{{ route('members.photo', $loan->member->memberId) }}" 
                                             alt="Photo de {{ $loan->member->firstName }} {{ $loan->member->lastName }}" 
                                             class="rounded-circle me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $loan->member->firstName }} {{ $loan->member->lastName }}</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-phone me-1"></i>
                                            {{ $loan->member->phoneNumber }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <strong>Montant:</strong><br>
                                <span class="text-primary fs-5">{{ round($loan->requestAmount, 2) }} USD</span><br>
                                <small class="text-muted">{{ $loan->loanMonths }} Mois</small>
                            </div>
                        </div>
                        
                        @if($loan->description)
                            <div class="mb-3">
                                <strong>Description:</strong><br>
                                <p class="text-muted">{{ $loan->description }}</p>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <strong>Adresse:</strong><br>
                            <small class="text-muted">{{ $loan->member->address }}</small>
                        </div>
                        
                        <!-- Informations financières -->
                        <div class="alert alert-info mb-3">
                            <h6><i class="fas fa-calculator me-2"></i>État du crédit</h6>
                            <div class="row">
                                <div class="col-4">
                                    <strong>Total dû:</strong><br>
                                    <span class="text-primary">{{ round($loan->totalAmountDue, 2) }} USD</span>
                                </div>
                                <div class="col-4">
                                    <strong>Remboursé:</strong><br>
                                    <span class="text-success">{{ round($loan->totalRepaid, 2) }} USD</span>
                                </div>
                                <div class="col-4">
                                    <strong>Reste dû:</strong><br>
                                    <span class="text-danger fw-bold">{{ round($loan->remainingAmount, 2) }} USD</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($loan->docPath)
                            <div class="mb-3">
                                <a href="{{ route('loans.document', $loan->loanDocId) }}" 
                                   target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-file me-1"></i>Voir le document
                                </a>
                            </div>
                        @endif
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('caissiere.loans.show', $loan->loanDocId) }}" 
                               class="btn btn-outline-primary me-2">
                                <i class="fas fa-eye me-1"></i>Voir détails
                            </a>
                            @if($loan->remainingAmount > 0)
                                <a href="{{ route('caissiere.repayments.create', ['loan_id' => $loan->loanDocId]) }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-money-bill-wave me-1"></i>Enregistrer un Remboursement
                                </a>
                            @else
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-check me-1"></i>Entièrement remboursé
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection