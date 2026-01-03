<!-- Dashboard pour tous les rôles -->
@can('view-dashboard-receptionniste')
    <a class="nav-link {{ request()->routeIs('receptionniste.dashboard') ? 'active' : '' }}" href="{{ route('receptionniste.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    @can('create-loan-requests')
        <a class="nav-link {{ request()->routeIs('receptionniste.loans.create') ? 'active' : '' }}" href="{{ route('receptionniste.loans.create') }}">
            <i class="fas fa-plus-circle"></i>Nouvelle demande
        </a>
    @endcan
    @can('view-loan-requests')
        <a class="nav-link {{ request()->routeIs('receptionniste.loans.index') ? 'active' : '' }}" href="{{ route('receptionniste.loans.index') }}">
            <i class="fas fa-list"></i>Demandes enregistrées
        </a>
    @endcan
@endcan

@can('view-dashboard-charge-credits')
    <a class="nav-link {{ request()->routeIs('charge_credits.dashboard') ? 'active' : '' }}" href="{{ route('charge_credits.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    @can('view-credit-validation')
        <a class="nav-link {{ request()->routeIs('charge_credits.validation') ? 'active' : '' }}" href="{{ route('charge_credits.validation') }}">
            <i class="fas fa-clipboard-check"></i>Validation crédits
        </a>
    @endcan
    @can('view-loan-requests')
        <a class="nav-link {{ request()->routeIs('charge_credits.loans.*') ? 'active' : '' }}" href="{{ route('charge_credits.loans.index') }}">
            <i class="fas fa-list"></i>Historique validations
        </a>
    @endcan
@endcan

@can('view-dashboard-gerant')
    <a class="nav-link {{ request()->routeIs('gerant.dashboard') ? 'active' : '' }}" href="{{ route('gerant.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    @can('view-final-validation')
        <a class="nav-link {{ request()->routeIs('gerant.final-validation') ? 'active' : '' }}" href="{{ route('gerant.final-validation') }}">
            <i class="fas fa-check-circle"></i>Validation finale
        </a>
    @endcan
    @can('view-loan-requests')
        <a class="nav-link {{ request()->routeIs('gerant.loans.*') ? 'active' : '' }}" href="{{ route('gerant.loans.index') }}">
            <i class="fas fa-list"></i>Historique validations
        </a>
    @endcan
    @can('view-repayments')
        <a class="nav-link {{ request()->routeIs('gerant.repayments.*') ? 'active' : '' }}" href="{{ route('gerant.repayments.index') }}">
            <i class="fas fa-money-bill-wave"></i>Remboursements
        </a>
    @endcan
@endcan

@can('view-dashboard-caissiere')
    <a class="nav-link {{ request()->routeIs('caissiere.dashboard') ? 'active' : '' }}" href="{{ route('caissiere.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    @can('view-repayments')
        <a class="nav-link {{ request()->routeIs('caissiere.repayments.*') ? 'active' : '' }}" href="{{ route('caissiere.repayments.index') }}">
            <i class="fas fa-calendar-alt"></i>Remboursements
        </a>
    @endcan
    @can('create-repayments')
        <a class="nav-link {{ request()->routeIs('caissiere.repayments.create') ? 'active' : '' }}" href="{{ route('caissiere.repayments.create') }}">
            <i class="fas fa-money-bill-wave"></i>Enregistrer remboursement
        </a>
    @endcan
    @can('view-penalties')
        <a class="nav-link {{ request()->routeIs('penalties.*') ? 'active' : '' }}" href="{{ route('penalties.index') }}">
            <i class="fas fa-exclamation-triangle"></i>Pénalités
        </a>
    @endcan
@endcan

@can('view-dashboard-directeur')
    <a class="nav-link {{ request()->routeIs('directeur.dashboard') ? 'active' : '' }}" href="{{ route('directeur.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    @can('view-loan-reports')
        <a class="nav-link {{ request()->routeIs('directeur.reports.*') ? 'active' : '' }}" href="{{ route('directeur.reports.loans') }}">
            <i class="fas fa-chart-bar"></i>Rapports crédits
        </a>
    @endcan
    @can('view-interest-reports')
        <a class="nav-link {{ request()->routeIs('directeur.reports.interests') ? 'active' : '' }}" href="{{ route('directeur.reports.interests') }}">
            <i class="fas fa-chart-line"></i>Rapports intérêts
        </a>
    @endcan
    @can('view-financial-reports')
        <a class="nav-link {{ request()->routeIs('directeur.reports.financial') ? 'active' : '' }}" href="{{ route('directeur.reports.financial') }}">
            <i class="fas fa-chart-pie"></i>Rapports financiers
        </a>
    @endcan
@endcan

@can('view-members')
    <a class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}" href="{{ route('members.index') }}">
        <i class="fas fa-users"></i>Gestion membres
    </a>
@endcan

<!-- Menu Trésorerie (Cashflow) -->
@php
    $hasCashflowPermission = auth()->user()->can('view-cashflow') || 
                             auth()->user()->can('create-cashflow') ||
                             auth()->user()->can('view-cashflow-reports');
@endphp
@if($hasCashflowPermission)
    <div class="nav-link treasury-menu-header" style="color: #bdc3c7; padding: 10px 25px; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 10px;" onclick="toggleTreasuryMenu()">
        <i class="fas fa-wallet me-2"></i>Trésorerie
    </div>
    <div class="treasury-menu-items">
        @can('view-cashflow')
            <a class="nav-link {{ request()->routeIs('cashflow.index') ? 'active' : '' }}" href="{{ route('cashflow.index') }}" style="padding-left: 50px;">
                <i class="fas fa-list me-2"></i>Transactions
            </a>
        @endcan
        @can('create-cashflow')
            <a class="nav-link {{ request()->routeIs('cashflow.create') ? 'active' : '' }}" href="{{ route('cashflow.create') }}" style="padding-left: 50px;">
                <i class="fas fa-plus-circle me-2"></i>Nouvelle transaction
            </a>
        @endcan
        @can('manage-cashflow-categories')
            <a class="nav-link {{ request()->routeIs('cashflow.categories.*') ? 'active' : '' }}" href="{{ route('cashflow.categories.index') }}" style="padding-left: 50px;">
                <i class="fas fa-tags me-2"></i>Catégories
            </a>
        @endcan
        @can('manage-cashflow-accounts')
            <a class="nav-link {{ request()->routeIs('cashflow.accounts.*') ? 'active' : '' }}" href="{{ route('cashflow.accounts.index') }}" style="padding-left: 50px;">
                <i class="fas fa-university me-2"></i>Comptes
            </a>
        @endcan
        @can('view-cashflow-reports')
            <a class="nav-link {{ request()->routeIs('cashflow.reports.*') ? 'active' : '' }}" href="{{ route('cashflow.reports.by-period') }}" style="padding-left: 50px;">
                <i class="fas fa-chart-line me-2"></i>Rapports
            </a>
        @endcan
    </div>
@endif

<!-- Menu Admin (Gestion des utilisateurs, rôles et permissions) -->
@can('manage-users')
    <div class="nav-link admin-menu-header" style="color: #bdc3c7; padding: 10px 25px; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 10px;" onclick="toggleAdminMenu()">
        <i class="fas fa-user-shield me-2"></i>Admin
    </div>
    <div class="admin-menu-items">
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" style="padding-left: 50px;">
            <i class="fas fa-users me-2"></i>Utilisateurs
        </a>
        <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}" style="padding-left: 50px;">
            <i class="fas fa-user-tag me-2"></i>Rôles
        </a>
        <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}" style="padding-left: 50px;">
            <i class="fas fa-key me-2"></i>Permissions
        </a>
    </div>
@endcan

<!-- Menu Rapport (accessible à tous avec permissions spécifiques) -->
@php
    // Vérifier si l'utilisateur a une permission de rapport
    $hasReportPermission = auth()->user()->can('view-report-credits-octroyes') || 
                             auth()->user()->can('view-report-credits-echus') || 
                             auth()->user()->can('view-report-global') || 
                             auth()->user()->can('view-report-bilan');
@endphp
@if($hasReportPermission)
    <div class="nav-link report-menu-header" style="color: #bdc3c7; padding: 10px 25px; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 10px;" onclick="toggleReportMenu()">
        <i class="fas fa-chart-bar me-2"></i>Rapport
    </div>
    <div class="report-menu-items">
        @can('view-report-credits-octroyes')
            <a class="nav-link {{ request()->routeIs('reports.credits-octroyes') ? 'active' : '' }}" href="{{ route('reports.credits-octroyes') }}" style="padding-left: 50px;">
                <i class="fas fa-file-invoice me-2"></i>Crédits octroyés
            </a>
        @endcan
        @can('view-report-credits-echus')
            <a class="nav-link {{ request()->routeIs('reports.credits-echus') ? 'active' : '' }}" href="{{ route('reports.credits-echus') }}" style="padding-left: 50px;">
                <i class="fas fa-exclamation-triangle me-2"></i>Crédits échus
            </a>
        @endcan
        @can('view-report-global')
            <a class="nav-link {{ request()->routeIs('reports.rapport-global') ? 'active' : '' }}" href="{{ route('reports.rapport-global') }}" style="padding-left: 50px;">
                <i class="fas fa-chart-pie me-2"></i>Rapport global
            </a>
        @endcan
        @can('view-report-bilan')
            <a class="nav-link {{ request()->routeIs('reports.bilan-par-periode') ? 'active' : '' }}" href="{{ route('reports.bilan-par-periode') }}" style="padding-left: 50px;">
                <i class="fas fa-balance-scale me-2"></i>Bilan crédit
            </a>
        @endcan
    </div>
@endif
