@extends('layouts.app')

@section('title', 'Modifier la catégorie')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-edit me-2"></i>Modifier la catégorie</h2>
                <a href="{{ route('cashflow.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de la catégorie</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cashflow.categories.update', $category->categoryId) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('categoryName') is-invalid @enderror" 
                                   id="categoryName" name="categoryName" 
                                   value="{{ old('categoryName', $category->categoryName) }}" required>
                            @error('categoryName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="categoryType" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('categoryType') is-invalid @enderror" 
                                    id="categoryType" name="categoryType" required>
                                <option value="">Sélectionner...</option>
                                <option value="income" {{ old('categoryType', $category->categoryType) == 'income' ? 'selected' : '' }}>Entrée</option>
                                <option value="expense" {{ old('categoryType', $category->categoryType) == 'expense' ? 'selected' : '' }}>Sortie</option>
                            </select>
                            @error('categoryType')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="parentCategoryId" class="form-label">Catégorie parente (optionnel)</label>
                            <select class="form-select @error('parentCategoryId') is-invalid @enderror" 
                                    id="parentCategoryId" name="parentCategoryId">
                                <option value="">Aucune (catégorie principale)</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->categoryId }}" {{ old('parentCategoryId', $category->parentCategoryId) == $parent->categoryId ? 'selected' : '' }}>
                                        {{ $parent->categoryName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parentCategoryId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActive" name="isActive" 
                                       {{ old('isActive', $category->isActive) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Catégorie active
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('cashflow.categories.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

