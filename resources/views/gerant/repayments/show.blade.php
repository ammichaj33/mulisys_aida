@extends('layouts.app')

@section('title', 'Détail du remboursement')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-receipt me-2"></i>Détail du remboursement</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('receipts.repayment', $repayment->loanRepaymentId) }}" target="_blank" class="btn btn-success">
                    <i class="fas fa-print me-2"></i>Imprimer reçu
                </a>
                <a href="{{ route('receipts.repayment.download', $repayment->loanRepaymentId) }}" class="btn btn-outline-success">
                    <i class="fas fa-download me-2"></i>Télécharger PDF
                </a>
                <a href="{{ route('gerant.repayments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations du remboursement</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Référence du remboursement</label>
                            <p class="form-control-plaintext">{{ $repayment->loanRepaymentId }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Montant</label>
                            <p class="form-control-plaintext h4 text-success">{{ round($repayment->amount, 2) }} USD</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date de remboursement</label>
                            <p class="form-control-plaintext">{{ date('d/m/Y', strtotime($repayment->repaymentDate)) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type de remboursement</label>
                            <p class="form-control-plaintext">{{ $repayment->repaymentType->repaymentName }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Enregistré par</label>
                            <p class="form-control-plaintext">{{ $repayment->user->name ?? 'Système' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date d'enregistrement</label>
                            <p class="form-control-plaintext">{{ date('d/m/Y H:i', strtotime($repayment->createdAt)) }}</p>
                        </div>
                    </div>
                </div>
                
                @if($repayment->description)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <p class="form-control-plaintext">{{ $repayment->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations du membre</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    @if($repayment->loanDoc->member->photo)
                        <img src="{{ route('members.photo', $repayment->loanDoc->member->memberId) }}" 
                             alt="Photo de {{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}" 
                             class="rounded-circle me-3" 
                             style="width: 60px; height: 60px; object-fit: cover;">
                    @else
                        <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user text-muted fa-2x"></i>
                        </div>
                    @endif
                    <div>
                        <h6 class="mb-0">{{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}</h6>
                        <small class="text-muted">{{ $repayment->loanDoc->member->phoneNumber }}</small>
                    </div>
                </div>
                
                <div class="mb-2">
                    <strong>Adresse:</strong><br>
                    <small class="text-muted">{{ $repayment->loanDoc->member->address }}</small>
                </div>
                
                <div class="mb-2">
                    <strong>Email:</strong><br>
                    <small class="text-muted">{{ $repayment->loanDoc->member->email ?? 'Non renseigné' }}</small>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informations du crédit</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Référence:</strong><br>
                    <span class="text-primary">{{ $repayment->loanDoc->refNumber }}</span>
                </div>
                
                <div class="mb-2">
                    <strong>Montant du crédit:</strong><br>
                    <span class="text-primary">{{ round($repayment->loanDoc->requestAmount, 2) }} USD</span>
                </div>
                
                <div class="mb-2">
                    <strong>Durée:</strong><br>
                    <span class="text-muted">{{ $repayment->loanDoc->loanMonths }} mois</span>
                </div>
                
                <div class="mb-2">
                    <strong>Taux d'intérêt:</strong><br>
                    <span class="text-muted">{{ $repayment->loanDoc->interestRate }}%</span>
                </div>
                
                <div class="mb-2">
                    <strong>Statut:</strong><br>
                    @php
                        $statusLabels = [
                            'draft' => 'Brouillon',
                            'accepted' => 'À valider',
                            'validated' => 'Validé',
                            'rejected' => 'Rejeté',
                            'done' => 'Terminé'
                        ];
                        $statusClass = [
                            'draft' => 'bg-secondary',
                            'accepted' => 'bg-warning',
                            'validated' => 'bg-primary',
                            'rejected' => 'bg-danger',
                            'done' => 'bg-success'
                        ];
                        $statusLabel = $statusLabels[$repayment->loanDoc->status] ?? ucfirst($repayment->loanDoc->status);
                        $statusClassValue = $statusClass[$repayment->loanDoc->status] ?? 'bg-secondary';
                    @endphp
                    <span class="badge {{ $statusClassValue }}">{{ $statusLabel }}</span>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
            </div>
            <div class="card-body">
                @if($repayment->loanDoc->status == 'validated')
                    <div class="d-grid gap-2">
                        <a href="{{ route('gerant.repayments.edit', $repayment->loanRepaymentId) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        <form method="POST" action="{{ route('gerant.repayments.destroy', $repayment->loanRepaymentId) }}" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce remboursement ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Les remboursements ne peuvent être modifiés ou supprimés que lorsque le crédit a le statut "Validé".
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

