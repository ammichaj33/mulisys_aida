@extends('layouts.app')

@section('title', 'Détail de la transaction')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-eye me-2"></i>Détail de la transaction</h2>
                <a href="{{ route('cashflow.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de la transaction</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Date</th>
                            <td>{{ $transaction->transactionDate->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                @if($transaction->transactionType == 'income')
                                    <span class="badge bg-success">Entrée</span>
                                @else
                                    <span class="badge bg-danger">Sortie</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Catégorie</th>
                            <td>{{ $transaction->category->categoryName }}</td>
                        </tr>
                        <tr>
                            <th>Compte</th>
                            <td>{{ $transaction->account->accountName ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Montant</th>
                            <td class="{{ $transaction->transactionType == 'income' ? 'text-success' : 'text-danger' }}">
                                <strong>{{ $transaction->transactionType == 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2, ',', ' ') }} USD</strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Mode de paiement</th>
                            <td>
                                @if($transaction->paymentMethod == 'cash')
                                    Espèces
                                @elseif($transaction->paymentMethod == 'bank')
                                    Banque
                                @elseif($transaction->paymentMethod == 'mobile_money')
                                    Mobile Money
                                @else
                                    Chèque
                                @endif
                            </td>
                        </tr>
                        @if($transaction->referenceNumber)
                        <tr>
                            <th>Numéro de référence</th>
                            <td>{{ $transaction->referenceNumber }}</td>
                        </tr>
                        @endif
                        @if($transaction->description)
                        <tr>
                            <th>Description</th>
                            <td>{{ $transaction->description }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Statut</th>
                            <td>
                                @if($transaction->status == 'confirmed')
                                    <span class="badge bg-success">Confirmé</span>
                                @elseif($transaction->status == 'pending')
                                    <span class="badge bg-warning">En attente</span>
                                @else
                                    <span class="badge bg-secondary">Annulé</span>
                                @endif
                            </td>
                        </tr>
                        @if($transaction->loanDoc)
                        <tr>
                            <th>Crédit lié</th>
                            <td>
                                <a href="{{ route('loans.show', $transaction->loanDoc->loanDocId) }}">
                                    {{ $transaction->loanDoc->refNumber }}
                                </a>
                            </td>
                        </tr>
                        @endif
                        @if($transaction->member)
                        <tr>
                            <th>Membre lié</th>
                            <td>{{ $transaction->member->firstName }} {{ $transaction->member->lastName }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Enregistré par</th>
                            <td>{{ $transaction->user->fullName }}</td>
                        </tr>
                        @if($transaction->confirmedByUser)
                        <tr>
                            <th>Confirmé par</th>
                            <td>{{ $transaction->confirmedByUser->fullName }} le {{ $transaction->confirmedAt->format('d/m/Y à H:i') }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="mt-3">
                        @can('edit-cashflow')
                            @if($transaction->status != 'cancelled')
                                <a href="{{ route('cashflow.edit', $transaction->cashflowTransactionId) }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </a>
                            @endif
                        @endcan
                        @can('confirm-cashflow')
                            @if($transaction->status == 'pending')
                                <form method="POST" action="{{ route('cashflow.confirm', $transaction->cashflowTransactionId) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Confirmer
                                    </button>
                                </form>
                            @endif
                        @endcan
                        @can('edit-cashflow')
                            @if($transaction->status != 'cancelled')
                                <form method="POST" action="{{ route('cashflow.cancel', $transaction->cashflowTransactionId) }}" 
                                      style="display: inline;"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette transaction ?');">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

