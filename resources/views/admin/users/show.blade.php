@extends('layouts.app')

@section('title', 'Détail de l\'utilisateur')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-user me-2"></i>Détail de l'utilisateur</h2>
                <div>
                    <a href="{{ route('admin.users.edit', $user->userId) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Nom complet</th>
                            <td>{{ $user->fullName }}</td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td>{{ $user->username }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Téléphone</th>
                            <td>{{ $user->phoneNumber ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                @if($user->isActive)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Date de création</th>
                            <td>{{ $user->createdAt->format('d/m/Y à H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Rôles et permissions</h5>
                </div>
                <div class="card-body">
                    <h6>Rôles assignés</h6>
                    <div class="mb-3">
                        @forelse($user->roles as $role)
                            <span class="badge bg-primary me-1">{{ ucfirst($role->name) }}</span>
                        @empty
                            <span class="text-muted">Aucun rôle assigné</span>
                        @endforelse
                    </div>

                    <h6>Permissions directes</h6>
                    <div class="mb-3">
                        @forelse($user->permissions as $permission)
                            <span class="badge bg-info me-1 mb-1">{{ $permission->name }}</span>
                        @empty
                            <span class="text-muted">Aucune permission directe</span>
                        @endforelse
                    </div>

                    <!-- Formulaire d'assignation de rôles -->
                    <form method="POST" action="{{ route('admin.users.assign-roles', $user->userId) }}" class="mb-3">
                        @csrf
                        <h6>Assigner des rôles</h6>
                        <div class="mb-2">
                            @foreach($allRoles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" 
                                           value="{{ $role->name }}" id="role_{{ $role->id }}"
                                           {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        {{ ucfirst($role->name) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour les rôles
                        </button>
                    </form>

                    <!-- Formulaire d'assignation de permissions -->
                    <form method="POST" action="{{ route('admin.users.assign-permissions', $user->userId) }}">
                        @csrf
                        <h6>Assigner des permissions directes</h6>
                        <div class="mb-2" style="max-height: 200px; overflow-y: auto;">
                            @foreach($allPermissions as $permission)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" 
                                           value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                           {{ $user->hasDirectPermission($permission->name) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $permission->id }}" style="font-size: 0.9rem;">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour les permissions
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

