@extends('layouts.app')

@section('title', 'Catégories de transactions')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-tags me-2"></i>Catégories de transactions</h2>
                <a href="{{ route('cashflow.categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvelle catégorie
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach(['income' => 'Entrées', 'expense' => 'Sorties'] as $type => $label)
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-{{ $type == 'income' ? 'success' : 'danger' }}">
                    <h5 class="mb-0 text-white">{{ $label }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories[$type] ?? [] as $category)
                            <tr>
                                <td>{{ $category->categoryName }}</td>
                                <td>{{ Str::limit($category->description, 50) }}</td>
                                <td>
                                    @if($category->isActive)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('cashflow.categories.edit', $category->categoryId) }}" 
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('cashflow.categories.destroy', $category->categoryId) }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune catégorie</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

