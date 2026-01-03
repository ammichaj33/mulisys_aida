@extends('layouts.app')

@section('title', 'Nouveau membre')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-plus me-2"></i>Nouveau membre</h2>
            <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations du membre</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" 
                                   value="{{ old('firstName') }}" required>
                            <small class="form-text text-muted">Prénom complet du membre</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" 
                                   value="{{ old('lastName') }}" required>
                            <small class="form-text text-muted">Nom de famille du membre</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phoneNumber" class="form-label">Numéro de téléphone *</label>
                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" 
                                   value="{{ old('phoneNumber') }}" required>
                            <small class="form-text text-muted">Numéro de téléphone principal</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ old('email') }}">
                            <small class="form-text text-muted">Adresse email (optionnelle)</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="institutionFrom" class="form-label">Institution d'origine *</label>
                        <input type="text" class="form-control" id="institutionFrom" name="institutionFrom" 
                               value="{{ old('institutionFrom') }}" placeholder="Ex: Université, École, Organisation..." required>
                        <small class="form-text text-muted">Institution ou organisation d'origine du membre</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birthDate" class="form-label">Date de naissance *</label>
                            <input type="date" class="form-control" id="birthDate" name="birthDate" 
                                   value="{{ old('birthDate') }}" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Genre *</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Sélectionner...</option>
                                <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" 
                                  placeholder="Adresse complète du membre..." required>{{ old('address') }}</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" 
                                   accept="image/jpeg,image/jpg,image/png,.jpg,.jpeg,.png,.JPG,.JPEG,.PNG">
                            <small class="form-text text-muted">Format: JPG, JPEG, PNG (max 1MB)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="idCard" class="form-label">Scan carte d'identité</label>
                            <input type="file" class="form-control" id="idCard" name="idCard" 
                                   accept=".pdf,image/jpeg,image/jpg,image/png,.jpg,.jpeg,.png,.JPG,.JPEG,.PNG,.PDF">
                            <small class="form-text text-muted">Format: PDF, JPG, JPEG, PNG (max 1MB)</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('members.index') }}" class="btn btn-secondary me-md-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer le membre
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Champs obligatoires</h6>
                    <p class="mb-0">Tous les champs marqués d'un astérisque (*) sont obligatoires.</p>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Vérification</h6>
                    <p class="mb-0">Vérifiez que le numéro de téléphone et l'email ne sont pas déjà utilisés par un autre membre.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

