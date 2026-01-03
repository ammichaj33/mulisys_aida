@extends('layouts.app')

@section('title', 'Validation des crédits')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-check me-2"></i>Validation des crédits</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-warning fs-6">
                    {{ $pendingLoans->count() }} demande(s) en attente
                </span>
                <a href="{{ route('charge_credits.dashboard') }}" class="btn btn-outline-secondary">
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
            <strong>Validation des crédits :</strong> Examinez et acceptez ou rejetez les demandes de crédit en attente.
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

@if ($pendingLoans->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
        <h5 class="text-success">Aucune demande en attente</h5>
        <p class="text-muted">Toutes les demandes ont été traitées.</p>
    </div>
@else
    <div class="row">
        @foreach ($pendingLoans as $loan)
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            {{ $loan->refNumber }}
                        </h6>
                        <span class="badge bg-warning">En attente</span>
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
                                <span class="text-primary fs-5">{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</span><br>
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
                        
                        @if($loan->docPath)
                            <div class="mb-3">
                                <a href="{{ route('loans.document', $loan->loanDocId) }}" 
                                   target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-file me-1"></i>Voir le document
                                </a>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('charge_credits.loans.validate', $loan->loanDocId) }}" class="validation-form">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="comments_{{ $loan->loanDocId }}" class="form-label">Commentaires</label>
                                <textarea class="form-control" id="comments_{{ $loan->loanDocId }}" 
                                          name="comments" rows="2" placeholder="Commentaires sur la décision..."></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="decision" value="rejected" 
                                        class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de rejeter cette demande ?')">
                                    <i class="fas fa-times me-1"></i>Rejeter
                                </button>
                                <button type="submit" name="decision" value="toreviewed" 
                                        class="btn btn-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>À réviser
                                </button>
                                <button type="submit" name="decision" value="accepted" 
                                        class="btn btn-success" onclick="return confirm('Êtes-vous sûr d\'accepter cette demande ?')">
                                    <i class="fas fa-check me-1"></i>Accepter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<script>
// Confirmation avant validation
document.querySelectorAll('.validation-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const decision = e.submitter.value;
        const loanId = this.querySelector('input[name="loanDocId"]')?.value;
        
        if (decision === 'accepted') {
            if (!confirm('Êtes-vous sûr d\'accepter cette demande de crédit ?')) {
                e.preventDefault();
            }
        } else if (decision === 'rejected') {
            if (!confirm('Êtes-vous sûr de rejeter cette demande de crédit ?')) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endsection
