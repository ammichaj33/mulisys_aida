@extends('layouts.app')

@section('title', 'Gestion des pénalités')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-exclamation-triangle me-2"></i>Gestion des pénalités
    </h1>
    <a href="{{ route('caissiere.dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
    </a>
</div>


<!-- Filtres -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="notPaid" {{ request('status') == 'notPaid' ? 'selected' : '' }}>Non payée</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payée</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Référence, nom, téléphone...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="{{ route('caissiere.penalties.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Liste des pénalités -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Pénalités
                    @if ($penalties->total() > 0)
                        <span class="badge bg-primary ms-2">{{ $penalties->total() }} résultat(s)</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if ($penalties->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune pénalité trouvée</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Référence crédit</th>
                                    <th>Membre</th>
                                    <th>Montant</th>
                                    <th>Raison</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penalties as $penalty)
                                    <tr>
                                        <td>{{ date('d/m/Y', strtotime($penalty->createdAt)) }}</td>
                                        <td>
                                            <strong>{{ $penalty->loanDoc->refNumber }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($penalty->loanDoc->member->photo)
                                                    <img src="{{ route('members.photo', $penalty->loanDoc->member->memberId) }}" 
                                                         alt="Photo de {{ $penalty->loanDoc->member->firstName }} {{ $penalty->loanDoc->member->lastName }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 35px; height: 35px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $penalty->loanDoc->member->firstName }} {{ $penalty->loanDoc->member->lastName }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $penalty->loanDoc->member->phoneNumber }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-danger">{{ number_format($penalty->amount, 2, ',', ' ') }} USD</strong>
                                        </td>
                                        <td>
                                            @php
                                                $reasonLabels = [
                                                    'retard' => 'Retard de paiement',
                                                    'montant_inferieur' => 'Montant inférieur'
                                                ];
                                                $reasonLabel = $reasonLabels[$penalty->reason] ?? $penalty->reason;
                                            @endphp
                                            <small>{{ $reasonLabel }}</small>
                                        </td>
                                        <td>
                                            @if($penalty->status == 'paid')
                                                <span class="badge bg-success">Payée</span>
                                            @else
                                                <span class="badge bg-warning">Non payée</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('caissiere.loans.show', $penalty->loanDocIdFk) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Voir le crédit">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($penalty->status == 'notPaid')
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#payPenaltyModal{{ $penalty->penalityId }}" 
                                                            title="Marquer comme payée">
                                                        <i class="fas fa-check"></i>
                                                    </button>

                                                    <!-- Modal de paiement -->
                                                    <div class="modal fade" id="payPenaltyModal{{ $penalty->penalityId }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Payer la pénalité</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST" action="{{ route('caissiere.penalties.pay', $penalty->penalityId) }}">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <p><strong>Crédit :</strong> {{ $penalty->loanDoc->refNumber }}</p>
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            @if($penalty->loanDoc->member->photo)
                                                                                <img src="{{ route('members.photo', $penalty->loanDoc->member->memberId) }}" 
                                                                                     alt="Photo de {{ $penalty->loanDoc->member->firstName }} {{ $penalty->loanDoc->member->lastName }}" 
                                                                                     class="rounded-circle me-2" 
                                                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                                            @else
                                                                                <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                                                     style="width: 40px; height: 40px;">
                                                                                    <i class="fas fa-user text-muted"></i>
                                                                                </div>
                                                                            @endif
                                                                            <div>
                                                                                <p class="mb-1"><strong>Membre :</strong> {{ $penalty->loanDoc->member->firstName }} {{ $penalty->loanDoc->member->lastName }}</p>
                                                                                <p class="mb-0"><strong>Montant :</strong> {{ number_format($penalty->amount, 2, ',', ' ') }} USD</p>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="payment_amount{{ $penalty->penalityId }}" class="form-label">Montant payé <span class="text-danger">*</span></label>
                                                                            <div class="input-group">
                                                                                <input type="number" step="0.01" class="form-control" 
                                                                                       id="payment_amount{{ $penalty->penalityId }}" 
                                                                                       name="payment_amount" 
                                                                                       value="{{ $penalty->amount }}" 
                                                                                       max="{{ $penalty->amount }}" 
                                                                                       required>
                                                                                <span class="input-group-text">USD</span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label for="payment_date{{ $penalty->penalityId }}" class="form-label">Date de paiement <span class="text-danger">*</span></label>
                                                                            <input type="date" class="form-control" 
                                                                                   id="payment_date{{ $penalty->penalityId }}" 
                                                                                   name="payment_date" 
                                                                                   value="{{ date('Y-m-d') }}" 
                                                                                   required>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label for="description{{ $penalty->penalityId }}" class="form-label">Commentaire</label>
                                                                            <textarea class="form-control" 
                                                                                      id="description{{ $penalty->penalityId }}" 
                                                                                      name="description" 
                                                                                      rows="2" 
                                                                                      placeholder="Commentaire optionnel..."></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                        <button type="submit" class="btn btn-success">
                                                                            <i class="fas fa-check me-2"></i>Confirmer le paiement
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $penalties->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


