@extends('layouts.app')

@section('title', 'Détails de la pénalité')

@section('content')
@if($penalty->isPartiallyPaid())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Paiement partiel : {{ round($penalty->paidAmount, 2) }} USD payés sur {{ round($penalty->total_amount, 2) }} USD.
        Il reste {{ round($penalty->remaining_amount, 2) }} USD à régulariser.
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-exclamation-triangle me-2"></i>Détails de la pénalité</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('penalties.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
                @if($penalty->status === 'notPaid')
                    <a href="#pay-penalty-form" class="btn btn-success">
                        <i class="fas fa-money-bill-wave me-2"></i>Payer la pénalité
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations de la pénalité</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID de la pénalité :</strong></td>
                                <td>{{ $penalty->penalityId }}</td>
                            </tr>
                            <tr>
                                <td><strong>Mois concerné :</strong></td>
                                <td>
                                    @php
                                        $displayMonth = $penalty->penaltyMonth;
                                        if (preg_match('/^(\d{2,4})-(\d{2})$/', $displayMonth, $m) && (int) $m[1] < 100) {
                                            $displayMonth = (2000 + (int) $m[1]) . '-' . $m[2];
                                        }
                                    @endphp
                                    {{ $displayMonth }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Montant total :</strong></td>
                                <td><span class="fw-bold h5">{{ round($penalty->total_amount, 2) }} USD</span></td>
                            </tr>
                            @if($penalty->isPartiallyPaid())
                                <tr>
                                    <td><strong>Reste à payer :</strong></td>
                                    <td><span class="text-danger fw-bold">{{ round($penalty->remaining_amount, 2) }} USD</span></td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Statut :</strong></td>
                                <td>
                                    @if($penalty->status === 'paid')
                                        <span class="badge bg-success fs-6">Payée</span>
                                    @elseif($penalty->isPartiallyPaid())
                                        <span class="badge bg-warning fs-6">Paiement partiel</span>
                                    @else
                                        <span class="badge bg-warning fs-6">En attente</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Raison :</strong></td>
                                <td>{{ ucfirst($penalty->reason) }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Date de création :</strong></td>
                                <td>{{ $penalty->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if($penalty->paidAt)
                                <tr>
                                    <td><strong>Date de paiement :</strong></td>
                                    <td>{{ $penalty->paidAt->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Montant payé :</strong></td>
                                    <td><span class="text-success fw-bold">{{ round($penalty->paidAmount, 2) }} USD</span></td>
                                </tr>
                            @endif
                            @if($penalty->creator)
                                <tr>
                                    <td><strong>Créée par :</strong></td>
                                    <td>{{ $penalty->creator->fullName }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <strong>Résumé du retard :</strong>
                    @if($penaltyDetails)
                        <div class="mt-2 p-3 bg-light rounded">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:40%">Échéance concernée</td>
                                    <td>
                                        <strong>n°{{ $penaltyDetails['installment_number'] }}</strong>
                                        du {{ $penaltyDetails['due_date']->format('d/m/Y') }}
                                        ({{ round($penaltyDetails['expected_amount'], 2) }} USD)
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Type de retard</td>
                                    <td>
                                        @if($penaltyDetails['is_consecutive'])
                                            <span class="badge bg-warning text-dark">Retard consécutif</span>
                                            <small class="text-muted d-block mt-1">
                                                Le mois précédent n'était pas encore soldé.
                                            </small>
                                        @else
                                            <span class="badge bg-info">Premier retard</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($penaltyDetails['is_consecutive'])
                                    <tr>
                                        <td class="text-muted">Impayés reportés<br><small>(tous les mois précédents)</small></td>
                                        <td>
                                            @if(!empty($penaltyDetails['previous_remaining']['breakdown']))
                                                <ul class="mb-2 ps-3">
                                                    @foreach($penaltyDetails['previous_remaining']['breakdown'] as $month)
                                                        @if($month['subtotal'] > 0)
                                                        <li>
                                                            <strong>{{ $month['label'] ?? 'Reste M-'.$month['installment_number'] }}</strong>
                                                            (échéance {{ $month['due_date']->format('d/m/Y') }}) :
                                                            <strong>{{ round($month['subtotal'], 2) }} USD</strong>
                                                            <small class="text-muted d-block">
                                                                {{ round($month['installment_remaining'], 2) }} échéance
                                                                + {{ round($month['penalty_remaining'], 2) }} pénalité
                                                            </small>
                                                        </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif
                                            <div>
                                                Total échéances impayées :
                                                <strong>{{ round($penaltyDetails['previous_remaining']['installment_remaining'], 2) }} USD</strong>
                                            </div>
                                            <div>
                                                Total pénalités impayées :
                                                <strong>{{ round($penaltyDetails['previous_remaining']['penalty_remaining'], 2) }} USD</strong>
                                            </div>
                                            <div class="mt-1">
                                                <strong>Total reporté : {{ round($penaltyDetails['previous_remaining']['total'], 2) }} USD</strong>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Montant de la pénalité</td>
                                    <td><strong class="text-danger">{{ round($penalty->remaining_amount > 0 ? $penalty->remaining_amount : $penalty->total_amount, 2) }} USD</strong></td>
                                </tr>
                            </table>
                        </div>
                    @elseif($penalty->description)
                        <p class="mt-2 p-3 bg-light rounded mb-0" style="white-space: pre-line;">{{ $penalty->description }}</p>
                    @else
                        <p class="mt-2 text-muted mb-0">Aucun détail disponible.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Formule de calcul de la pénalité -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Formule de calcul de la pénalité</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Formules appliquées :</h6>
                        <div class="bg-light p-3 rounded mb-3">
                            <p class="mb-2"><code><strong>M-1 :</strong> (Capital M-1 + Intérêt M-1) × {{ config('penalties.penalty_rate', 10) }}%</code></p>
                            <p class="mb-2"><code><strong>M-2 :</strong> (Reste M-1 + Capital M-2 + Intérêt M-2) × {{ config('penalties.penalty_rate', 10) }}%</code></p>
                            <p class="mb-0"><code><strong>M-3 :</strong> (Reste M-1 + Reste M-2 + Capital M-3 + Intérêt M-3) × {{ config('penalties.penalty_rate', 10) }}%</code></p>
                            <small class="text-muted d-block mt-2">Reste M-x = échéance M-x impayée + pénalité M-x impayée</small>
                        </div>
                        
                        <h6 class="text-primary">Paramètres utilisés :</h6>
                        <ul class="list-unstyled">
                            <li><strong>Taux de pénalité :</strong> {{ config('penalties.penalty_rate', 10) }}%</li>
                            <li><strong>Jours de tolérance :</strong> {{ config('penalties.tolerance_days', 30) }} jours</li>
                            <li><strong>Reste M-x :</strong> Échéance impayée + pénalité impayée du mois M-x</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        @if($penaltyDetails)
                            <h6 class="text-primary">Calcul détaillé de cette pénalité :</h6>
                            <div class="bg-light p-3 rounded">
                                <p><strong>Données de cette pénalité :</strong></p>
                                <ul class="mb-2">
                                    <li>Date d'échéance : <strong>{{ $penaltyDetails['due_date']->format('d/m/Y') }}</strong></li>
                                    <li>Montant échéance : <strong>{{ round($penaltyDetails['expected_amount'], 2) }} USD</strong></li>
                                    <li>Total remboursé (global) : <strong>{{ round($penaltyDetails['total_repaid'], 2) }} USD</strong></li>
                                    <li>Fin de tolérance : <strong>{{ $penaltyDetails['tolerance_end_date']->format('d/m/Y') }}</strong></li>
                                    <li>Remboursé pendant la tolérance : <strong>{{ round($penaltyDetails['total_repaid_before_due'], 2) }} USD</strong></li>
                                    <li>Amortissement mois : <strong>{{ round($penaltyDetails['current_month_capital'], 2) }} USD</strong></li>
                                    <li>Intérêt mois : <strong>{{ round($penaltyDetails['current_month_interest'], 2) }} USD</strong></li>
                                    @if($penaltyDetails['is_consecutive'])
                                        <li>Total échéances impayées (mois précédents) : <strong>{{ round($penaltyDetails['previous_remaining']['installment_remaining'], 2) }} USD</strong></li>
                                        <li>Total pénalités impayées (mois précédents) : <strong>{{ round($penaltyDetails['previous_remaining']['penalty_remaining'], 2) }} USD</strong></li>
                                        <li>Reste cumulé reporté : <strong>{{ round($penaltyDetails['previous_remaining']['total'], 2) }} USD</strong></li>
                                    @endif
                                    <li>Mois de retard : <strong>{{ $penaltyDetails['months_overdue'] }} mois</strong></li>
                                    <li>Taux : <strong>{{ $penaltyDetails['penalty_rate'] }}%</strong></li>
                                </ul>
                                <p><strong>Formule appliquée :</strong></p>
                                <p class="mb-1">
                                    <code>{{ $penaltyDetails['formula_labelled'] ?? $penaltyDetails['formula'] }}</code>
                                </p>
                                <p><strong>Calcul :</strong></p>
                                <p class="mb-0">
                                    <code>Pénalité M-{{ $penaltyDetails['installment_number'] }} = {{ $penaltyDetails['formula'] }} = {{ round($penaltyDetails['calculated_penalty'], 2) }} USD</code>
                                </p>
                            </div>
                        @else
                            <h6 class="text-primary">Exemple de calcul :</h6>
                            <div class="bg-light p-3 rounded">
                                <p><strong>Si :</strong></p>
                                <ul class="mb-2">
                                    <li>Capital restant dû : <strong>200 USD</strong></li>
                                    <li>Mois de retard : <strong>1 mois</strong></li>
                                    <li>Taux mensuel : <strong>1%</strong></li>
                                </ul>
                                <p><strong>Alors :</strong></p>
                                <p class="mb-0">
                                    <code>Pénalité = 200 × 1% × 1 = 2.00 USD</code>
                                </p>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <h6 class="text-success">Logique appliquée :</h6>
                            <ul class="small">
                                <li><strong>Reste M-x</strong> = échéance M-x impayée + pénalité M-x impayée</li>
                                <li>Chaque pénalité cumule les <strong>Reste M-1</strong> jusqu'au mois précédent</li>
                                <li>Seuls les remboursements <strong>pendant la tolérance</strong> de M-n (échéance + 30 jours) réduisent le Reste M-x</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        @if($penalty->status === 'notPaid')
        <div class="card mb-4 border-success" id="pay-penalty-form">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payer la pénalité</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('penalties.pay', $penalty->penalityId) }}">
                    @csrf
                    @if($errors->has('amount') || $errors->has('payment_date'))
                        <div class="alert alert-danger py-2">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    @if($penalty->isPartiallyPaid())
                        <div class="alert alert-info py-2 mb-3">
                            Déjà payé : <strong>{{ round($penalty->paidAmount, 2) }} USD</strong><br>
                            Reste à payer : <strong>{{ round($penalty->remaining_amount, 2) }} USD</strong>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant à payer <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" max="{{ round($penalty->remaining_amount, 2) }}"
                               class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount"
                               value="{{ old('amount', round($penalty->remaining_amount, 2)) }}" required>
                        <small class="text-muted">Entre 0,01 et {{ round($penalty->remaining_amount, 2) }} USD maximum.</small>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Date de paiement <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date"
                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check me-2"></i>Confirmer le paiement
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Informations du crédit -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Crédit associé</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Référence :</strong></td>
                        <td>{{ $penalty->loanDoc->refNumber }}</td>
                    </tr>
                    <tr>
                        <td><strong>Montant :</strong></td>
                        <td>{{ round($penalty->loanDoc->requestAmount, 2) }} USD</td>
                    </tr>
                    <tr>
                        <td><strong>Durée :</strong></td>
                        <td>{{ $penalty->loanDoc->loanMonths }} mois</td>
                    </tr>
                    <tr>
                        <td><strong>Taux :</strong></td>
                        <td>{{ $penalty->loanDoc->interestRate }}%</td>
                    </tr>
                    <tr>
                        <td><strong>Statut :</strong></td>
                        <td>
                            @switch($penalty->loanDoc->status)
                                @case('draft')
                                    <span class="badge bg-secondary">Brouillon</span>
                                    @break
                                @case('accepted')
                                    <span class="badge bg-warning">À valider</span>
                                    @break
                                @case('validated')
                                    <span class="badge bg-success">Validé</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">Rejeté</span>
                                    @break
                                @case('done')
                                    <span class="badge bg-info">Terminé</span>
                                    @break
                            @endswitch
                        </td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="{{ route('loans.show', $penalty->loanDoc->loanDocId) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-2"></i>Voir le crédit
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Informations du membre -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Membre</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    @if($penalty->loanDoc->member->photo)
                        <img src="{{ route('members.photo', $penalty->loanDoc->member->memberId) }}" 
                             alt="Photo de {{ $penalty->loanDoc->member->firstName }} {{ $penalty->loanDoc->member->lastName }}" 
                             class="rounded-circle me-3" 
                             style="width: 50px; height: 50px; object-fit: cover;">
                    @else
                        <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                             style="width: 50px; height: 50px;">
                            <i class="fas fa-user text-muted"></i>
                        </div>
                    @endif
                    <div>
                        <strong>{{ $penalty->loanDoc->member->firstName }} {{ $penalty->loanDoc->member->lastName }}</strong>
                        <br>
                        <small class="text-muted">{{ $penalty->loanDoc->member->phoneNumber }}</small>
                    </div>
                </div>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Email :</strong></td>
                        <td>{{ $penalty->loanDoc->member->email ?? 'Non renseigné' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Adresse :</strong></td>
                        <td>{{ $penalty->loanDoc->member->address ?? 'Non renseignée' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Institution :</strong></td>
                        <td>{{ $penalty->loanDoc->member->institutionFrom ?? 'Non renseignée' }}</td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="{{ route('members.show', $penalty->loanDoc->member->memberId) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-user me-2"></i>Voir le membre
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
