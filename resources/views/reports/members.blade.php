@extends('layouts.app')

@section('title', 'Rapport des membres')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>Rapport des membres</h2>
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
                <form method="GET" action="{{ route('reports.members') }}" class="row g-3">
                    <div class="col-md-8">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nom, prénom, téléphone...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="{{ route('reports.members') }}" class="btn btn-outline-secondary">
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
                    <i class="fas fa-list me-2"></i>Liste des membres
                    @if ($members->count() > 0)
                        <span class="badge bg-primary ms-2">{{ $members->count() }} résultat(s)</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($members->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun membre trouvé</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Membre</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Crédits</th>
                                    <th>Montant total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $member)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($member->photo)
                                                    <img src="{{ route('members.photo', $member->memberId) }}" 
                                                         alt="Photo de {{ $member->firstName }} {{ $member->lastName }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 35px; height: 35px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $member->firstName }} {{ $member->lastName }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $member->address }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $member->phoneNumber }}</td>
                                        <td>{{ $member->email ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $member->loan_docs_count }}</span>
                                        </td>
                                        <td>{{ number_format($member->loan_docs_sum_request_amount, 2, ',', ' ') }} USD</td>
                                        <td>
                                            <a href="{{ route('members.show', $member->memberId) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $members->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

