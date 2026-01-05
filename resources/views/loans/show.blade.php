@extends('layouts.app')

@section('title', 'Détails de la demande')

@section('content')
@php
    // Déterminer la route de retour selon les permissions
    $backRoute = route('loans.index');
    if (auth()->user()->can('view-dashboard-receptionniste')) {
        $backRoute = route('receptionniste.dashboard');
    } elseif (auth()->user()->can('view-dashboard-gerant')) {
        $backRoute = route('gerant.dashboard');
    } elseif (auth()->user()->can('view-dashboard-caissiere')) {
        $backRoute = route('caissiere.dashboard');
    } elseif (auth()->user()->can('view-dashboard-charge-credits')) {
        $backRoute = route('charge_credits.dashboard');
    } elseif (auth()->user()->can('view-dashboard-directeur')) {
        $backRoute = route('directeur.dashboard');
    }
@endphp

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt me-2"></i>Détails de la demande</h2>
            <div>
                <a href="{{ $backRoute }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                    @if(auth()->user()->can('view-dashboard-gerant') || auth()->user()->can('view-dashboard-caissiere'))
                        au dashboard
                    @endif
                </a>
                @can('edit-loan-requests')
                    @if($loan->status == 'draft' || $loan->status == 'toreviewed')
                        @if(auth()->user()->can('view-dashboard-receptionniste'))
                            <a href="{{ route('receptionniste.loans.edit', $loan->loanDocId) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Modifier
                            </a>
                        @endif
                    @endif
                @endcan
                @can('create-repayments')
                    @if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere'))
                        <a href="{{ route('caissiere.repayments.create', ['loan_id' => $loan->loanDocId]) }}" class="btn btn-primary">
                            <i class="fas fa-money-bill-wave me-2"></i>Enregistrer remboursement
                        </a>
                        @if($remainingAmount > 0)
                        <a href="{{ route('caissiere.loans.early-repayment', $loan->loanDocId) }}" class="btn btn-warning">
                            <i class="fas fa-clock me-2"></i>Remboursement anticipé
                        </a>
                        @endif
                    @endif
                @endcan
                @can('validate-credits')
                    @if($loan->status == 'accepted' && auth()->user()->can('view-dashboard-charge-credits'))
                        <a href="{{ route('charge_credits.loans.validate', $loan->loanDocId) }}" class="btn btn-primary">
                            <i class="fas fa-check-circle me-2"></i>Valider
                        </a>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>

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
                        <strong>Statut:</strong>
                        <p>
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
                        </p>
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
        <div class="card">
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
                        @if($totalCancelledInterest > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Intérêts annulés:</span>
                            <span class="text-success">-{{ $calculationService->formatMoney($totalCancelledInterest) }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Montant total à rembourser:</strong>
                            <strong class="text-primary">{{ $calculationService->formatMoney($totalAmountDue) }}</strong>
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
                
                @if($loan->member->address)
                <div class="mb-3">
                    <strong>Adresse:</strong>
                    <p>{{ $loan->member->address }}</p>
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
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
            </div>
            <div class="card-body">
                @can('generate-repayment-pdf')
                <div class="d-grid gap-2 mb-3">
                    <a href="{{ route('loans.schedule.pdf', $loan->loanDocId) }}" 
                       class="btn btn-success" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Générer l'échéancier PDF
                    </a>
                </div>
                @endcan

                @can('edit-loan-requests')
                    @if($loan->status == 'draft' || $loan->status == 'toreviewed')
                        @if(auth()->user()->can('view-dashboard-receptionniste'))
                            <div class="d-grid gap-2">
                                <a href="{{ route('receptionniste.loans.edit', $loan->loanDocId) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Modifier la demande
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette demande a été traitée et ne peut plus être modifiée.
                        </div>
                    @endif
                @endcan

                @can('create-repayments')
                    @if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere'))
                        <div class="d-grid gap-2">
                            <a href="{{ route('caissiere.repayments.create', ['loan_id' => $loan->loanDocId]) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-money-bill-wave me-2"></i>Enregistrer remboursement
                            </a>
                            @if($remainingAmount > 0)
                            <a href="{{ route('caissiere.loans.early-repayment', $loan->loanDocId) }}" 
                               class="btn btn-warning">
                                <i class="fas fa-clock me-2"></i>Remboursement anticipé
                            </a>
                            @endif
                            <a href="{{ route('caissiere.loans.schedule', $loan->loanDocId) }}" 
                               class="btn btn-outline-info">
                                <i class="fas fa-calendar-alt me-2"></i>Calendrier de remboursement
                            </a>
                        </div>
                    @elseif($loan->status != 'validated' && auth()->user()->can('view-dashboard-caissiere'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette demande n'est pas encore validée pour le remboursement.
                        </div>
                    @endif
                @endcan

                @can('final-validate-credits')
                    @if($loan->status == 'accepted' && auth()->user()->can('view-dashboard-gerant'))
                        <div class="d-grid gap-2">
                            <a href="{{ route('gerant.loans.edit', $loan->loanDocId) }}" 
                               class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Modifier dossier
                            </a>
                            <a href="{{ route('gerant.final-validation') }}" 
                               class="btn btn-primary">
                                <i class="fas fa-gavel me-2"></i>Validation finale
                            </a>
                        </div>
                    @elseif($loan->status == 'rejected' && auth()->user()->can('view-dashboard-gerant'))
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-ban me-2"></i>
                            Cette demande a été rejetée et ne peut plus être modifiée.
                        </div>
                    @elseif(($loan->status == 'validated' || $loan->status == 'done') && auth()->user()->can('view-dashboard-gerant'))
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            Cette demande a été validée. Vous pouvez modifier le montant du remboursement si nécessaire.
                        </div>
                    @endif
                @endcan

                @can('validate-credits')
                    @if($loan->status == 'accepted' && auth()->user()->can('view-dashboard-charge-credits'))
                        <div class="d-grid gap-2">
                            <a href="{{ route('charge_credits.loans.validate', $loan->loanDocId) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-check-circle me-2"></i>Valider
                            </a>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Historique des remboursements (avant le calendrier) -->
@can('delete-repayments')
    @if(auth()->user()->can('view-dashboard-gerant') && $loan->loanRepayments->count() > 0)
        @include('loans._repayment-history-gerant')
    @endif
@endcan

@can('view-repayments')
    @if(!auth()->user()->can('view-dashboard-gerant') || !auth()->user()->can('delete-repayments'))
        @include('loans._repayment-history')
    @endif
@endcan

<!-- Calendrier de remboursement détaillé -->
@can('view-repayment-schedule')
@include('loans._repayment-schedule')
@endcan

<!-- Commentaires de validation (après le calendrier) -->
@if($loan->validationHistory->isNotEmpty())
@include('loans._validation-comments')
@endif

@can('create-repayments')
    @if(auth()->user()->can('view-dashboard-caissiere'))
        @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script>
        function printRepaymentReceipt(repaymentId, repaymentType, amount, date, recordedBy) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [80, 200]
            });
            
            doc.setFont('helvetica');
            doc.setFontSize(8);
            
            let y = 10;
            const lineHeight = 4;
            const pageWidth = 80;
            
            doc.setFontSize(10);
            doc.setFont('helvetica', 'bold');
            doc.text('MICROCREDIT SYSTEM', pageWidth/2, y, { align: 'center' });
            y += lineHeight + 2;
            
            doc.setFontSize(8);
            doc.setFont('helvetica', 'bold');
            doc.text('REÇU DE REMBOURSEMENT', pageWidth/2, y, { align: 'center' });
            y += lineHeight + 2;
            
            doc.setFont('helvetica', 'normal');
            doc.text(`Date: ${new Date().toLocaleDateString('fr-FR')}`, pageWidth/2, y, { align: 'center' });
            y += lineHeight;
            doc.text(`Heure: ${new Date().toLocaleTimeString('fr-FR')}`, pageWidth/2, y, { align: 'center' });
            y += lineHeight + 3;
            
            doc.line(5, y, pageWidth-5, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('N° Dossier:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text('{{ $loan->refNumber }}', 25, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Membre:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text('{{ $loan->member->firstName }} {{ $loan->member->lastName }}', 20, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Téléphone:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text('{{ $loan->member->phoneNumber }}', 25, y);
            y += lineHeight + 2;
            
            doc.line(5, y, pageWidth-5, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Type:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text(repaymentType, 20, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Montant:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text(amount, 25, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Date remb.:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text(date, 30, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('Enregistré par:', 5, y);
            doc.setFont('helvetica', 'normal');
            doc.text(recordedBy, 35, y);
            y += lineHeight + 2;
            
            doc.line(5, y, pageWidth-5, y);
            y += lineHeight;
            
            doc.setFont('helvetica', 'bold');
            doc.text('STATUT: {{ ['draft' => 'Brouillon', 'accepted' => 'Accepté', 'rejected' => 'Rejeté', 'validated' => 'Validé', 'done' => 'Terminé', 'toreviewed' => 'À réviser', 'finalized' => 'Finalisé'][$loan->status] }}', pageWidth/2, y, { align: 'center' });
            y += lineHeight + 3;
            
            doc.line(5, y, pageWidth-5, y);
            y += lineHeight;
            
            doc.setFontSize(6);
            doc.text('Merci pour votre confiance', pageWidth/2, y, { align: 'center' });
            y += lineHeight;
            doc.text('www.microcredit-system.com', pageWidth/2, y, { align: 'center' });
            
            doc.save(`recu_remboursement_${repaymentId}_${new Date().getTime()}.pdf`);
        }
        </script>
        @endpush
    @endif
@endcan
@endsection
