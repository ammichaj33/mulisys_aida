@extends('layouts.app')

@section('title', 'Modifier le compte')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-edit me-2"></i>Modifier le compte</h2>
                <a href="{{ route('cashflow.accounts.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du compte</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cashflow.accounts.update', $account->accountId) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="accountName" class="form-label">Nom du compte <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('accountName') is-invalid @enderror" 
                                   id="accountName" name="accountName" 
                                   value="{{ old('accountName', $account->accountName) }}" required>
                            @error('accountName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="accountType" class="form-label">Type de compte <span class="text-danger">*</span></label>
                            <select class="form-select @error('accountType') is-invalid @enderror" 
                                    id="accountType" name="accountType" required>
                                <option value="">Sélectionner...</option>
                                <option value="cash" {{ old('accountType', $account->accountType) == 'cash' ? 'selected' : '' }}>Espèces</option>
                                <option value="bank" {{ old('accountType', $account->accountType) == 'bank' ? 'selected' : '' }}>Banque</option>
                                <option value="mobile_money" {{ old('accountType', $account->accountType) == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            </select>
                            @error('accountType')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="initialBalance" class="form-label">Solde initial <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" 
                                   class="form-control @error('initialBalance') is-invalid @enderror" 
                                   id="initialBalance" name="initialBalance" 
                                   value="{{ old('initialBalance', $account->initialBalance) }}" required>
                            @error('initialBalance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Le solde actuel sera recalculé après la modification.</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $account->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActive" name="isActive" 
                                       {{ old('isActive', $account->isActive) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Compte actif
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('cashflow.accounts.index') }}" class="btn btn-secondary">Annuler</a>
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

