@extends('layouts.app')

@section('title', 'Remboursement anticipé')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clock me-2"></i>Remboursement anticipé</h2>
            <a href="{{ route('caissiere.loans.show', $loan->loanDocId) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <!-- Informations du crédit -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations du crédit</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Référence:</strong>
                        <p class="text-primary">{{ $loan->refNumber }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Membre:</strong>
                        <p>{{ $loan->member->firstName }} {{ $loan->member->lastName }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Montant du crédit:</strong>
                        <p class="text-success h5">{{ $calculationService->formatMoney($loan->requestAmount) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Durée:</strong>
                        <p>{{ $loan->loanMonths }} mois</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Taux d'intérêt:</strong>
                        <p>{{ $loan->interestRate }}% par mois</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Date d'octroi:</strong>
                        <p>{{ $loan->submitDate->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculs du remboursement anticipé -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Calcul du remboursement anticipé</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Remboursement anticipé :</strong> 
                    Le membre rembourse le capital restant + les intérêts dus jusqu'à la date de remboursement. 
                    Les intérêts des mois non encore échus seront annulés.
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Capital restant à rembourser</h6>
                                <h4 class="text-primary mb-0">{{ $calculationService->formatMoney($earlyRepaymentDetails['capitalRestant']) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Intérêts dus jusqu'à la date</h6>
                                <h4 class="text-info mb-0">{{ $calculationService->formatMoney($earlyRepaymentDetails['interetsDus']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                @if($earlyRepaymentDetails['interetsACanceler'] > 0)
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>Intérêts qui seront annulés</h6>
                    <p class="mb-2"><strong>Total des intérêts annulés:</strong> 
                        <span class="text-success h5">{{ $calculationService->formatMoney($earlyRepaymentDetails['interetsACanceler']) }}</span>
                    </p>
                    <p class="mb-0"><small>Les intérêts des mois suivants seront annulés :</small></p>
                    <ul class="mb-0 mt-2">
                        @foreach($earlyRepaymentDetails['monthsToCancel'] as $month)
                            <li>{{ \Carbon\Carbon::parse($month['date'])->format('F Y') }} : {{ $calculationService->formatMoney($month['interest']) }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-2">Montant total à payer</h6>
                        <h2 class="mb-0">{{ $calculationService->formatMoney($earlyRepaymentDetails['montantTotal']) }}</h2>
                        <small class="d-block mt-2">
                            Capital restant ({{ $calculationService->formatMoney($earlyRepaymentDetails['capitalRestant']) }}) 
                            + Intérêts dus ({{ $calculationService->formatMoney($earlyRepaymentDetails['interetsDus']) }})
                            @if($totalCancelledInterest > 0)
                                - Intérêts déjà annulés ({{ $calculationService->formatMoney($totalCancelledInterest) }})
                            @endif
                        </small>
                    </div>
                </div>

                <div class="mt-3">
                    <p class="text-muted mb-0">
                        <small>
                            <i class="fas fa-clock me-1"></i>
                            Mois écoulés: {{ $earlyRepaymentDetails['monthsElapsed'] }} / {{ $earlyRepaymentDetails['totalMonths'] }}
                        </small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Formulaire de remboursement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('caissiere.loans.early-repayment.process', $loan->loanDocId) }}" id="earlyRepaymentForm">
                    @csrf

                    <div class="mb-3">
                        <label for="repaymentDate" class="form-label">Date de remboursement <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('repaymentDate') is-invalid @enderror" 
                               id="repaymentDate" 
                               name="repaymentDate" 
                               value="{{ old('repaymentDate', now()->format('Y-m-d')) }}"
                               required
                               onchange="updateCalculation()">
                        @error('repaymentDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">La date de remboursement ne peut pas être antérieure à la date d'octroi ({{ $loan->submitDate->format('d/m/Y') }})</small>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('amount') is-invalid @enderror" 
                               id="amount" 
                               name="amount" 
                               step="0.01"
                               value="{{ old('amount', $earlyRepaymentDetails['montantTotal']) }}"
                               required
                               readonly>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Ce montant est calculé automatiquement pour un remboursement anticipé complet</small>
                    </div>

                    <div class="mb-3">
                        <label for="repaymentTypeIdFk" class="form-label">Type de remboursement <span class="text-danger">*</span></label>
                        @if($earlyRepaymentType)
                            <input type="hidden" name="repaymentTypeIdFk" value="{{ $earlyRepaymentType->repaymentTypeID }}">
                            <input type="text" 
                                   class="form-control" 
                                   id="repaymentTypeIdFk_display" 
                                   value="{{ $earlyRepaymentType->repaymentName }}" 
                                   readonly>
                            <small class="form-text text-muted">Type de remboursement fixé pour un remboursement anticipé</small>
                        @else
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Erreur :</strong> Aucun type de remboursement "Remboursement anticipé" trouvé dans la base de données. 
                                Veuillez contacter l'administrateur pour créer ce type dans la table repaymenttype.
                            </div>
                        @endif
                        @error('repaymentTypeIdFk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description (optionnel)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Remboursement anticipé...">{{ old('description', 'Remboursement anticipé') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input @error('confirm_early_repayment') is-invalid @enderror" 
                                   type="checkbox" 
                                   id="confirm_early_repayment" 
                                   name="confirm_early_repayment" 
                                   value="1"
                                   required>
                            <label class="form-check-label" for="confirm_early_repayment">
                                Je confirme que le membre souhaite effectuer un remboursement anticipé complet. 
                                Les intérêts des mois non échus ({{ count($earlyRepaymentDetails['monthsToCancel']) }} mois) seront annulés.
                            </label>
                            @error('confirm_early_repayment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Enregistrer le remboursement anticipé
                        </button>
                        <a href="{{ route('caissiere.loans.show', $loan->loanDocId) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Résumé -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Résumé</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Montant total dû:</strong>
                    <p class="text-primary h5">{{ $calculationService->formatMoney($totalAmountDue) }}</p>
                </div>
                <div class="mb-3">
                    <strong>Déjà remboursé:</strong>
                    <p class="text-success">{{ $calculationService->formatMoney($totalRepaid) }}</p>
                </div>
                <div class="mb-3">
                    <strong>Reste dû:</strong>
                    <p class="text-warning h5">{{ $calculationService->formatMoney($remainingAmount) }}</p>
                </div>
                <hr>
                <div class="mb-3">
                    <strong>Montant du remboursement anticipé:</strong>
                    <p class="text-primary h4">{{ $calculationService->formatMoney($earlyRepaymentDetails['montantTotal']) }}</p>
                </div>
                @if($earlyRepaymentDetails['interetsACanceler'] > 0)
                <div class="alert alert-success">
                    <strong>Économie pour le membre:</strong>
                    <p class="mb-0 h5">{{ $calculationService->formatMoney($earlyRepaymentDetails['interetsACanceler']) }}</p>
                    <small>Intérêts annulés</small>
                </div>
                @endif
            </div>
        </div>

        <!-- Avertissement -->
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Important</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Le remboursement anticipé doit couvrir <strong>tout le capital restant</strong></li>
                    <li>Les intérêts des mois non échus seront <strong>automatiquement annulés</strong></li>
                    <li>Le crédit sera marqué comme <strong>terminé</strong> après l'enregistrement</li>
                    <li>Cette action est <strong>irréversible</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateCalculation() {
    // Note: Pour une implémentation complète, on pourrait faire un appel AJAX
    // pour recalculer les montants en fonction de la date sélectionnée
    // Pour l'instant, on garde le montant initial
    const repaymentDate = document.getElementById('repaymentDate').value;
    const loanStartDate = '{{ $loan->submitDate->format('Y-m-d') }}';
    
    if (repaymentDate < loanStartDate) {
        alert('La date de remboursement ne peut pas être antérieure à la date d\'octroi du crédit.');
        document.getElementById('repaymentDate').value = '{{ now()->format('Y-m-d') }}';
    }
}

document.getElementById('earlyRepaymentForm').addEventListener('submit', function(e) {
    const confirmCheckbox = document.getElementById('confirm_early_repayment');
    if (!confirmCheckbox.checked) {
        e.preventDefault();
        alert('Veuillez confirmer le remboursement anticipé en cochant la case de confirmation.');
        return false;
    }
    
    return confirm('Êtes-vous sûr de vouloir enregistrer ce remboursement anticipé ? Cette action est irréversible.');
});
</script>
@endpush
@endsection

