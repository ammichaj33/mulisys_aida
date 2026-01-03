@extends('layouts.app')

@section('title', 'Échéancier de remboursement')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar me-2"></i>Échéancier de remboursement</h2>
            <a href="{{ route('loans.show', $loan->loanDocId) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations du crédit</h5>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <label><i class="fas fa-hashtag me-2"></i>Référence:</label>
                    <span><strong>{{ $loan->refNumber }}</strong></span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-user me-2"></i>Membre:</label>
                    <div class="d-flex align-items-center">
                        @if($loan->member->photo)
                            <img src="{{ route('members.photo', $loan->member->memberId) }}" 
                                 alt="Photo de {{ $loan->member->firstName }} {{ $loan->member->lastName }}" 
                                 class="rounded-circle me-2" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user text-muted"></i>
                            </div>
                        @endif
                        <span>{{ $loan->member->firstName }} {{ $loan->member->lastName }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-dollar-sign me-2"></i>Montant:</label>
                    <span>{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-calendar me-2"></i>Durée:</label>
                    <span>{{ $loan->loanMonths }} mois</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-percentage me-2"></i>Taux d'intérêt:</label>
                    <span>{{ $loan->interestRate }}%</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Calculs financiers</h5>
            </div>
            <div class="card-body">
                @php
                    $totalInterest = $loan->requestAmount * ($loan->interestRate / 100) * $loan->loanMonths;
                    $totalAmount = $loan->requestAmount + $totalInterest;
                    $totalRepaid = $loan->loanRepayments->sum('amount');
                    $remainingAmount = $totalAmount - $totalRepaid;
                @endphp
                
                <div class="info-item">
                    <label><i class="fas fa-dollar-sign me-2"></i>Montant principal:</label>
                    <span>{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-percentage me-2"></i>Intérêts calculés:</label>
                    <span>{{ number_format($totalInterest, 2, ',', ' ') }} USD</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-calculator me-2"></i>Montant total:</label>
                    <span><strong>{{ number_format($totalAmount, 2, ',', ' ') }} USD</strong></span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-check-circle me-2"></i>Remboursé:</label>
                    <span class="text-success">{{ number_format($totalRepaid, 2, ',', ' ') }} USD</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-clock me-2"></i>Restant:</label>
                    <span class="text-warning">{{ number_format($remainingAmount, 2, ',', ' ') }} USD</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <label><i class="fas fa-calendar-alt me-2"></i>Échéances:</label>
                    <span>{{ count($schedule) }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-check-circle me-2"></i>Remboursements:</label>
                    <span>{{ $loan->loanRepayments->count() }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-percentage me-2"></i>Progression:</label>
                    <span>{{ number_format(($totalRepaid / $totalAmount) * 100, 1) }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar me-2"></i>Échéancier de remboursement
                    <span class="badge bg-primary ms-2">{{ count($schedule) }} échéance(s)</span>
                </h5>
            </div>
            <div class="card-body">
                @if(empty($schedule))
                    <div class="text-center py-4">
                        <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun échéancier calculé</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Échéance</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Restant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedule as $installment)
                                    <tr>
                                        <td>{{ $installment['installment'] }}</td>
                                        <td>{{ $installment['date']->format('d/m/Y') }}</td>
                                        <td>{{ number_format($installment['amount'], 2, ',', ' ') }} USD</td>
                                        <td>{{ number_format($installment['remaining'], 2, ',', ' ') }} USD</td>
                                        <td>
                                            @if($installment['date'] < now())
                                                <span class="badge bg-danger">En retard</span>
                                            @elseif($installment['date'] == now()->format('Y-m-d'))
                                                <span class="badge bg-warning">Aujourd'hui</span>
                                            @else
                                                <span class="badge bg-info">À venir</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
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