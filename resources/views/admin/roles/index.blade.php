@extends('layouts.app')

@section('title', 'Gestion des rôles')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-user-tag me-2"></i>Gestion des rôles</h2>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau rôle
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Liste des rôles</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nom</th>
                                    <th>Permissions</th>
                                    <th>Utilisateurs</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td>
                                        <strong>{{ ucfirst($role->name) }}</strong>
                                    </td>
                                    <td>
                                        @if($role->permissions->count() > 0)
                                            <span class="badge bg-info">{{ $role->permissions->count() }} permission(s)</span>
                                        @else
                                            <span class="text-muted">Aucune permission</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $role->users_count }} utilisateur(s)</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.roles.show', $role->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($role->users_count == 0)
                                                <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucun rôle trouvé</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

