@extends('layouts.app')

@section('title', 'Détails du membre')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user me-2"></i>Détails du membre</h2>
            <div>
                <a href="{{ route('members.edit', $member->memberId) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-2"></i>Modifier
                </a>
                <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations personnelles</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($member->photo)
                        <div class="avatar-circle">
                            <img src="{{ route('members.photo', $member->memberId) }}" alt="Photo de {{ $member->firstName }} {{ $member->lastName }}" 
                                 class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                    @else
                        <div class="avatar-circle">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    @endif
                    <h4 class="mt-2">{{ $member->firstName }} {{ $member->lastName }}</h4>
                    <span class="badge {{ $member->gender == 'M' ? 'bg-primary' : 'bg-pink' }}">
                        {{ $member->gender == 'M' ? 'Masculin' : 'Féminin' }}
                    </span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-phone me-2"></i>Téléphone:</label>
                    <span>{{ $member->phoneNumber }}</span>
                </div>
                
                @if($member->email)
                <div class="info-item">
                    <label><i class="fas fa-envelope me-2"></i>Email:</label>
                    <span>{{ $member->email }}</span>
                </div>
                @endif
                
                <div class="info-item">
                    <label><i class="fas fa-university me-2"></i>Institution d'origine:</label>
                    <span>{{ $member->institutionFrom }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-birthday-cake me-2"></i>Date de naissance:</label>
                    <span>{{ $member->birthDate->format('d/m/Y') }}</span>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-map-marker-alt me-2"></i>Adresse:</label>
                    <span>{{ $member->address }}</span>
                </div>
                
                @if($member->idCard)
                <div class="info-item">
                    <label><i class="fas fa-id-card me-2"></i>Carte d'identité:</label>
                    <a href="{{ route('members.idcard', $member->memberId) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Voir le document
                    </a>
                </div>
                @endif
                
                <div class="info-item">
                    <label><i class="fas fa-calendar me-2"></i>Membre depuis:</label>
                    <span>{{ $member->createdAt->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Historique des crédits
                    <span class="badge bg-primary ms-2">{{ $member->loanDocs->count() }} crédit(s)</span>
                </h5>
            </div>
            <div class="card-body">
                @if($member->loanDocs->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun crédit enregistré pour ce membre</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($member->loanDocs as $loan)
                                    <tr>
                                        <td><strong>{{ $loan->refNumber }}</strong></td>
                                        <td>{{ number_format($loan->requestAmount, 2, ',', ' ') }} USD</td>
                                        <td>
                                            @php
                                                $statusClass = '';
                                                switch ($loan->status) {
                                                    case 'draft': $statusClass = 'bg-secondary'; break;
                                                    case 'accepted': $statusClass = 'bg-success'; break;
                                                    case 'rejected': $statusClass = 'bg-danger'; break;
                                                    case 'validated': $statusClass = 'bg-primary'; break;
                                                    case 'done': $statusClass = 'bg-info'; break;
                                                    case 'toreviewed': $statusClass = 'bg-warning'; break;
                                                }
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ ['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser', 'finalized' => 'Finalisé'][$loan->status] }}
                                            </span>
                                        </td>
                                        <td>{{ $loan->submitDate->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('loans.show', $loan->loanDocId) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin: 0 auto;
}

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

