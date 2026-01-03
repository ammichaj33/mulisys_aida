@extends('layouts.app')

@section('title', 'Gestion des remboursements')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-receipt me-2"></i>Gestion des remboursements</h2>
            <a href="{{ route('repayments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouveau remboursement
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('repayments.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-4">
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
                            <a href="{{ route('repayments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Liste des remboursements
                    @if ($repayments->count() > 0)
                        <span class="badge bg-primary ms-2">{{ $repayments->count() }} résultat(s)</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($repayments->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun remboursement trouvé</p>
                        <a href="{{ route('repayments.create') }}" class="btn btn-primary">Créer le premier remboursement</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Crédit</th>
                                    <th>Membre</th>
                                    <th>Montant</th>
                                    <th>Type</th>
                                    <th>Enregistré par</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($repayments as $repayment)
                                    <tr>
                                        <td>{{ $repayment->repaymentDate->format('d/m/Y') }}</td>
                                        <td>
                                            <strong>{{ $repayment->loanDoc->refNumber }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($repayment->loanDoc->member->photo)
                                                    <img src="{{ route('members.photo', $repayment->loanDoc->member->memberId) }}" 
                                                         alt="Photo de {{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 35px; height: 35px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $repayment->loanDoc->member->firstName }} {{ $repayment->loanDoc->member->lastName }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $repayment->loanDoc->member->phoneNumber }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ number_format($repayment->amount, 2, ',', ' ') }} USD</td>
                                        <td>
                                            <span class="badge bg-info">{{ $repayment->repaymentType->repaymentName }}</span>
                                        </td>
                                        <td>{{ $repayment->user->fullName }}</td>
                                        <td>
                                            <a href="{{ route('repayments.show', $repayment->loanRepaymentId) }}" 
                                               class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($repayment->loanDoc->status != 'validated' && $repayment->loanDoc->status != 'rejected')
                                                <a href="{{ route('repayments.edit', $repayment->loanRepaymentId) }}" 
                                                   class="btn btn-sm btn-outline-warning me-1" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            <form method="POST" action="{{ route('repayments.destroy', $repayment->loanRepaymentId) }}" 
                                                  style="display: inline-block;" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce remboursement ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $repayments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

