<!-- Historique des remboursements pour autres rôles -->
<div class="row mt-4" id="repayments">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des remboursements</h5>
                <span class="badge bg-primary">{{ $loan->loanRepayments->count() }} remboursement(s)</span>
            </div>
            <div class="card-body">
                @if($loan->loanRepayments->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun remboursement enregistré</p>
                        @can('create-repayments')
                            @if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere'))
                                <a href="{{ route('caissiere.repayments.create', ['loan_id' => $loan->loanDocId]) }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Enregistrer le premier remboursement
                                </a>
                            @endif
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Type</th>
                                    <th>Enregistré par</th>
                                    @can('create-repayments')
                                        @if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere'))
                                            <th>Date d'enregistrement</th>
                                            <th>Actions</th>
                                        @endif
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loan->loanRepayments as $repayment)
                                    <tr>
                                        <td>
                                            <strong>{{ $repayment->repaymentDate->format('d/m/Y') }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">{{ $calculationService->formatMoney($repayment->amount) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $repayment->repaymentType->repaymentName ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $repayment->user->fullName ?? 'Système' }}</small>
                                        </td>
                                        @can('create-repayments')
                                            @if($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere'))
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $repayment->createdAt->format('d/m/Y H:i') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="{{ route('receipts.repayment', $repayment->loanRepaymentId) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-success" 
                                                       title="Imprimer reçu">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </td>
                                            @endif
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="{{ ($loan->status == 'validated' && auth()->user()->can('view-dashboard-caissiere')) ? '5' : '4' }}" class="text-end">Total remboursé:</th>
                                    <th class="text-success">{{ $calculationService->formatMoney($totalRepaid) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

