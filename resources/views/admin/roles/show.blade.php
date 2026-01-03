@extends('layouts.app')

@section('title', 'Détail du rôle')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-user-tag me-2"></i>Détail du rôle</h2>
                <div>
                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
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
                    <h5 class="mb-0">Informations du rôle</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Nom</th>
                            <td><strong>{{ ucfirst($role->name) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nombre de permissions</th>
                            <td><span class="badge bg-info">{{ $role->permissions->count() }}</span></td>
                        </tr>
                        <tr>
                            <th>Nombre d'utilisateurs</th>
                            <td><span class="badge bg-primary">{{ $role->users->count() }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Permissions assignées</h5>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        <div style="max-height: 300px; overflow-y: auto;">
                            @foreach($role->permissions as $permission)
                                <span class="badge bg-info me-1 mb-1">{{ $permission->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Aucune permission assignée</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Utilisateurs avec ce rôle</h5>
                </div>
                <div class="card-body">
                    @if($role->users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom complet</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $user)
                                    <tr>
                                        <td>{{ $user->fullName }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email ?? '-' }}</td>
                                        <td>
                                            @if($user->isActive)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-secondary">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user->userId) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Aucun utilisateur avec ce rôle</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

