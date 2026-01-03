@extends('layouts.app')

@section('title', 'Gestion des permissions')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-key me-2"></i>Gestion des permissions</h2>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($permissions as $group => $groupPermissions)
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ ucfirst($group) }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($groupPermissions as $permission)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $permission->name }}</span>
                            <span class="badge bg-info">
                                {{ $permission->roles->count() }} rôle(s)
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

