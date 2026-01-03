@extends('layouts.app')

@section('title', 'Nouvelle transaction')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-plus-circle me-2"></i>Nouvelle transaction</h2>
                <a href="{{ route('cashflow.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de la transaction</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cashflow.store') }}" id="transactionForm">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="transactionDate" class="form-label">Date de la transaction <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('transactionDate') is-invalid @enderror" 
                                       id="transactionDate" name="transactionDate" 
                                       value="{{ old('transactionDate', date('Y-m-d')) }}" required>
                                @error('transactionDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="transactionType" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('transactionType') is-invalid @enderror" 
                                        id="transactionType" name="transactionType" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="income" {{ old('transactionType') == 'income' ? 'selected' : '' }}>Entrée</option>
                                    <option value="expense" {{ old('transactionType') == 'expense' ? 'selected' : '' }}>Sortie</option>
                                </select>
                                @error('transactionType')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="categoryIdFk" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select @error('categoryIdFk') is-invalid @enderror" 
                                        id="categoryIdFk" name="categoryIdFk" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->categoryId }}" 
                                                data-type="{{ $category->categoryType }}"
                                                {{ old('categoryIdFk') == $category->categoryId ? 'selected' : '' }}>
                                            {{ $category->categoryName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoryIdFk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="accountIdFk" class="form-label">Compte</label>
                                <select class="form-select @error('accountIdFk') is-invalid @enderror" 
                                        id="accountIdFk" name="accountIdFk">
                                    <option value="">Aucun compte</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->accountId }}" {{ old('accountIdFk') == $account->accountId ? 'selected' : '' }}>
                                            {{ $account->accountName }} ({{ $account->accountType }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('accountIdFk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Montant <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" 
                                       value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="paymentMethod" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                <select class="form-select @error('paymentMethod') is-invalid @enderror" 
                                        id="paymentMethod" name="paymentMethod" required>
                                    <option value="cash" {{ old('paymentMethod') == 'cash' ? 'selected' : '' }}>Espèces</option>
                                    <option value="bank" {{ old('paymentMethod') == 'bank' ? 'selected' : '' }}>Banque</option>
                                    <option value="mobile_money" {{ old('paymentMethod') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                    <option value="check" {{ old('paymentMethod') == 'check' ? 'selected' : '' }}>Chèque</option>
                                </select>
                                @error('paymentMethod')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="referenceNumber" class="form-label">Numéro de référence</label>
                                <input type="text" class="form-control @error('referenceNumber') is-invalid @enderror" 
                                       id="referenceNumber" name="referenceNumber" 
                                       value="{{ old('referenceNumber') }}">
                                @error('referenceNumber')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="confirmed" {{ old('status', 'confirmed') == 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="loanDocIdFk" class="form-label">Lier à un crédit (optionnel)</label>
                                <select class="form-select @error('loanDocIdFk') is-invalid @enderror" 
                                        id="loanDocIdFk" name="loanDocIdFk">
                                    <option value="">Aucun crédit</option>
                                    @foreach($loans as $loan)
                                        <option value="{{ $loan->loanDocId }}" {{ old('loanDocIdFk') == $loan->loanDocId ? 'selected' : '' }}>
                                            {{ $loan->refNumber }} - {{ $loan->member->firstName }} {{ $loan->member->lastName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('loanDocIdFk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="memberIdFk" class="form-label">Lier à un membre (optionnel)</label>
                                <select class="form-select @error('memberIdFk') is-invalid @enderror" 
                                        id="memberIdFk" name="memberIdFk">
                                    <option value="">Aucun membre</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->memberId }}" {{ old('memberIdFk') == $member->memberId ? 'selected' : '' }}>
                                            {{ $member->firstName }} {{ $member->lastName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('memberIdFk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('cashflow.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filtrer les catégories selon le type de transaction
    document.getElementById('transactionType').addEventListener('change', function() {
        const transactionType = this.value;
        const categorySelect = document.getElementById('categoryIdFk');
        const options = categorySelect.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                return;
            }
            const categoryType = option.getAttribute('data-type');
            if (transactionType === '' || categoryType === transactionType) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Réinitialiser la sélection si nécessaire
        if (categorySelect.value && categorySelect.options[categorySelect.selectedIndex].style.display === 'none') {
            categorySelect.value = '';
        }
    });
</script>
@endpush

