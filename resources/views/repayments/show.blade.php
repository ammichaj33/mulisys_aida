@extends('layouts.app')

@section('title', 'Détails du remboursement')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-receipt me-2"></i>Détails du remboursement</h2>
            <div>
                @if($repayment->loanDoc->status != 'validated' && $repayment->loanDoc->status != 'rejected')
                    <a href="{{ route('repayments.edit', $repayment->loanRepaymentId) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                @endif
                <a href="{{ route('repayments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Informations du remboursement</h5>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <label><i class="fas fa-hashtag me-2"></i>ID:</label>
                    <span><strong>{{ $repayment->loanRepaymentId }}</strong></span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-dollar-sign me-2"></i>Montant:</label>
                    <span><strong>{{ number_format($repayment->amount, 2, ',', ' ') }} USD</strong></span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-calendar me-2"></i>Date de remboursement:</label>
                    <span>{{ $repayment->repaymentDate->format('d/m/Y') }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-tag me-2"></i>Type:</label>
                    <span class="badge bg-info">{{ $repayment->repaymentType->repaymentName }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-user me-2"></i>Enregistré par:</label>
                    <span>{{ $repayment->user->fullName }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-calendar-alt me-2"></i>Date d'enregistrement:</label>
                    <span>{{ $repayment->createdAt->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informations du crédit</h5>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <label><i class="fas fa-hashtag me-2"></i>Référence:</label>
                    <span><strong>{{ $repayment->loanDoc->refNumber }}</strong></span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-user me-2"></i>Membre:</label>
                    <div class="d-flex align-items-center">
                        @if($repayment->loanDoc->member->photo)
                            <img src="{{ route('members.photo', $repayment->loanDoc->member->memberId) }}" 
                                 alt="Photo de {{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}" 
                                 class="rounded-circle me-2" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user text-muted"></i>
                            </div>
                        @endif
                        <span>{{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-phone me-2"></i>Téléphone:</label>
                    <span>{{ $repayment->loanDoc->member->phoneNumber }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-dollar-sign me-2"></i>Montant du crédit:</label>
                    <span>{{ number_format($repayment->loanDoc->requestAmount, 2, ',', ' ') }} USD</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-flag me-2"></i>Statut:</label>
                    @php
                        $statusClass = '';
                        switch ($repayment->loanDoc->status) {
                            case 'draft': $statusClass = 'bg-secondary'; break;
                            case 'accepted': $statusClass = 'bg-success'; break;
                            case 'rejected': $statusClass = 'bg-danger'; break;
                            case 'validated': $statusClass = 'bg-primary'; break;
                            case 'done': $statusClass = 'bg-info'; break;
                            case 'toreviewed': $statusClass = 'bg-warning'; break;
                        }
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        {{ ['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser', 'finalized' => 'Finalisé'][$repayment->loanDoc->status] }}
                    </span>
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

