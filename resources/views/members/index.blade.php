@extends('layouts.app')

@section('title', 'Gestion des membres')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>Gestion des membres</h2>
            <div class="d-flex gap-2">
                @php
                    $backRoute = route('dashboard');
                    if (auth()->user()->can('view-dashboard-receptionniste')) {
                        $backRoute = route('receptionniste.dashboard');
                    } elseif (auth()->user()->can('view-dashboard-charge-credits')) {
                        $backRoute = route('charge_credits.dashboard');
                    } elseif (auth()->user()->can('view-dashboard-gerant')) {
                        $backRoute = route('gerant.dashboard');
                    } elseif (auth()->user()->can('view-dashboard-caissiere')) {
                        $backRoute = route('caissiere.dashboard');
                    } elseif (auth()->user()->can('view-dashboard-directeur')) {
                        $backRoute = route('directeur.dashboard');
                    }
                @endphp
                @if($backRoute != route('dashboard'))
                    <a href="{{ $backRoute }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
                    </a>
                @endif
                <a href="{{ route('members.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau membre
                </a>
            </div>
        </div>
    </div>
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
                    <div class="col-md-3">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nom, prénom, téléphone, email, institution...">
                    </div>
                    <div class="col-md-2">
                        <label for="gender" class="form-label">Genre</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">Tous</option>
                            <option value="M" {{ (request('gender') == 'M') ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ (request('gender') == 'F') ? 'selected' : '' }}>Féminin</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous</option>
                            <option value="active" {{ (request('status') == 'active') ? 'selected' : '' }}>Actif</option>
                            <option value="inactive" {{ (request('status') == 'inactive') ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="birth_year" class="form-label">Année de naissance</label>
                        <select class="form-select" id="birth_year" name="birth_year">
                            <option value="">Toutes</option>
                            @for($year = date('Y'); $year >= 1950; $year--)
                                <option value="{{ $year }}" {{ (request('birth_year') == $year) ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
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
                    @if ($members->total() > 0)
                        <span class="badge bg-primary ms-2">{{ $members->total() }} résultat(s)</span>
                    @endif
                </h5>
                @if (!empty(request('search')) || !empty(request('gender')) || !empty(request('status')) || !empty(request('birth_year')))
                    <div class="text-muted">
                        <small>Filtres actifs</small>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if($members->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun membre enregistré</p>
                        <a href="{{ route('members.create') }}" class="btn btn-primary">Créer le premier membre</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover data-table">
                            <thead>
                                <tr>
                                    <th>Nom complet</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Genre</th>
                                    <th>Date de naissance</th>
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
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $member->firstName }} {{ $member->lastName }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $member->phoneNumber }}</td>
                                        <td>{{ $member->email ?? '-' }}</td>
                                        <td>{{ $member->gender == 'M' ? 'Masculin' : 'Féminin' }}</td>
                                        <td>{{ $member->birthDate->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('members.show', $member->memberId) }}" 
                                               class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('members.edit', $member->memberId) }}" 
                                               class="btn btn-sm btn-outline-warning me-1" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('members.destroy', $member->memberId) }}" 
                                                  style="display: inline-block;" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver ce membre ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Désactiver">
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
                        {{ $members->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
