<!-- Historique des remboursements pour gérant -->
<div class="row mt-4" id="repayments">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des remboursements</h5>
                <span class="badge bg-primary">{{ $loan->loanRepayments->count() }} remboursement(s)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Type</th>
                                <th>Enregistré par</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loan->loanRepayments->sortByDesc('repaymentDate') as $repayment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($repayment->repaymentDate)->format('d/m/Y') }}</td>
                                <td class="text-success fw-bold">{{ number_format($repayment->amount, 2, ',', ' ') }} USD</td>
                                <td>{{ $repayment->repaymentType->repaymentName ?? 'N/A' }}</td>
                                <td>{{ $repayment->user->name ?? 'Système' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('gerant.repayments.show', $repayment->loanRepaymentId) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($loan->status == 'validated')
                                            <a href="{{ route('gerant.repayments.edit', $repayment->loanRepaymentId) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('receipts.repayment', $repayment->loanRepaymentId) }}" 
                                           target="_blank" class="btn btn-sm btn-outline-success" title="Imprimer">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if($loan->status == 'validated')
                                            <form method="POST" action="{{ route('gerant.repayments.destroy', $repayment->loanRepaymentId) }}" 
                                                  style="display: inline;" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce remboursement ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="1"><strong>TOTAL REMBOURSÉ</strong></th>
                                <th class="text-success">{{ number_format($totalRepaid, 2, ',', ' ') }} USD</th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

