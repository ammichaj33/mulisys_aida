<!-- Dashboard pour tous les rôles -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard-receptionniste')): ?>
    <a class="nav-link <?php echo e(request()->routeIs('receptionniste.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('receptionniste.dashboard')); ?>">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-loan-requests')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('receptionniste.loans.create') ? 'active' : ''); ?>" href="<?php echo e(route('receptionniste.loans.create')); ?>">
            <i class="fas fa-plus-circle"></i>Nouvelle demande
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-loan-requests')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('receptionniste.loans.index') ? 'active' : ''); ?>" href="<?php echo e(route('receptionniste.loans.index')); ?>">
            <i class="fas fa-list"></i>Demandes enregistrées
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard-charge-credits')): ?>
    <a class="nav-link <?php echo e(request()->routeIs('charge_credits.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('charge_credits.dashboard')); ?>">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-credit-validation')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('charge_credits.validation') ? 'active' : ''); ?>" href="<?php echo e(route('charge_credits.validation')); ?>">
            <i class="fas fa-clipboard-check"></i>Validation crédits
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-loan-requests')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('charge_credits.loans.*') ? 'active' : ''); ?>" href="<?php echo e(route('charge_credits.loans.index')); ?>">
            <i class="fas fa-list"></i>Historique validations
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard-gerant')): ?>
    <a class="nav-link <?php echo e(request()->routeIs('gerant.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('gerant.dashboard')); ?>">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-final-validation')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('gerant.final-validation') ? 'active' : ''); ?>" href="<?php echo e(route('gerant.final-validation')); ?>">
            <i class="fas fa-check-circle"></i>Validation finale
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-loan-requests')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('gerant.loans.*') ? 'active' : ''); ?>" href="<?php echo e(route('gerant.loans.index')); ?>">
            <i class="fas fa-list"></i>Historique validations
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-repayments')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('gerant.repayments.*') ? 'active' : ''); ?>" href="<?php echo e(route('gerant.repayments.index')); ?>">
            <i class="fas fa-money-bill-wave"></i>Remboursements
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard-caissiere')): ?>
    <a class="nav-link <?php echo e(request()->routeIs('caissiere.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('caissiere.dashboard')); ?>">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-repayments')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('caissiere.repayments.*') ? 'active' : ''); ?>" href="<?php echo e(route('caissiere.repayments.index')); ?>">
            <i class="fas fa-calendar-alt"></i>Remboursements
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-repayments')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('caissiere.repayments.create') ? 'active' : ''); ?>" href="<?php echo e(route('caissiere.repayments.create')); ?>">
            <i class="fas fa-money-bill-wave"></i>Enregistrer remboursement
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-penalties')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('penalties.*') ? 'active' : ''); ?>" href="<?php echo e(route('penalties.index')); ?>">
            <i class="fas fa-exclamation-triangle"></i>Pénalités
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard-directeur')): ?>
    <a class="nav-link <?php echo e(request()->routeIs('directeur.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('directeur.dashboard')); ?>">
        <i class="fas fa-tachometer-alt"></i>Dashboard
    </a>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-loan-reports')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('directeur.reports.*') ? 'active' : ''); ?>" href="<?php echo e(route('directeur.reports.loans')); ?>">
            <i class="fas fa-chart-bar"></i>Rapports crédits
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-interest-reports')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('directeur.reports.interests') ? 'active' : ''); ?>" href="<?php echo e(route('directeur.reports.interests')); ?>">
            <i class="fas fa-chart-line"></i>Rapports intérêts
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-financial-reports')): ?>
        <a class="nav-link <?php echo e(request()->routeIs('directeur.reports.financial') ? 'active' : ''); ?>" href="<?php echo e(route('directeur.reports.financial')); ?>">
            <i class="fas fa-chart-pie"></i>Rapports financiers
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-members')): ?>
    <a class="nav-link <?php echo e(request()->routeIs('members.*') ? 'active' : ''); ?>" href="<?php echo e(route('members.index')); ?>">
        <i class="fas fa-users"></i>Gestion membres
    </a>
<?php endif; ?>

<!-- Menu Trésorerie (Cashflow) -->
<?php
    $hasCashflowPermission = auth()->user()->can('view-cashflow') || 
                             auth()->user()->can('create-cashflow') ||
                             auth()->user()->can('view-cashflow-reports');
?>
<?php if($hasCashflowPermission): ?>
    <div class="nav-link treasury-menu-header" style="color: #bdc3c7; padding: 10px 25px; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 10px;" onclick="toggleTreasuryMenu()">
        <i class="fas fa-wallet me-2"></i>Trésorerie
    </div>
    <div class="treasury-menu-items">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-cashflow')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('cashflow.index') ? 'active' : ''); ?>" href="<?php echo e(route('cashflow.index')); ?>" style="padding-left: 50px;">
                <i class="fas fa-list me-2"></i>Transactions
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-cashflow')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('cashflow.create') ? 'active' : ''); ?>" href="<?php echo e(route('cashflow.create')); ?>" style="padding-left: 50px;">
                <i class="fas fa-plus-circle me-2"></i>Nouvelle transaction
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-cashflow-categories')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('cashflow.categories.*') ? 'active' : ''); ?>" href="<?php echo e(route('cashflow.categories.index')); ?>" style="padding-left: 50px;">
                <i class="fas fa-tags me-2"></i>Catégories
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-cashflow-accounts')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('cashflow.accounts.*') ? 'active' : ''); ?>" href="<?php echo e(route('cashflow.accounts.index')); ?>" style="padding-left: 50px;">
                <i class="fas fa-university me-2"></i>Comptes
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-cashflow-reports')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('cashflow.reports.*') ? 'active' : ''); ?>" href="<?php echo e(route('cashflow.reports.by-period')); ?>" style="padding-left: 50px;">
                <i class="fas fa-chart-line me-2"></i>Rapports
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Menu Admin (Gestion des utilisateurs, rôles et permissions) -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-users')): ?>
    <div class="nav-link admin-menu-header" style="color: #bdc3c7; padding: 10px 25px; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 10px;" onclick="toggleAdminMenu()">
        <i class="fas fa-user-shield me-2"></i>Admin
    </div>
    <div class="admin-menu-items">
        <a class="nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users.index')); ?>" style="padding-left: 50px;">
            <i class="fas fa-users me-2"></i>Utilisateurs
        </a>
        <a class="nav-link <?php echo e(request()->routeIs('admin.roles.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.roles.index')); ?>" style="padding-left: 50px;">
            <i class="fas fa-user-tag me-2"></i>Rôles
        </a>
        <a class="nav-link <?php echo e(request()->routeIs('admin.permissions.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.permissions.index')); ?>" style="padding-left: 50px;">
            <i class="fas fa-key me-2"></i>Permissions
        </a>
    </div>
<?php endif; ?>

<!-- Menu Rapport (accessible à tous avec permissions spécifiques) -->
<?php
    // Vérifier si l'utilisateur a une permission de rapport
    $hasReportPermission = auth()->user()->can('view-report-credits-octroyes') || 
                             auth()->user()->can('view-report-credits-echus') || 
                             auth()->user()->can('view-report-global') || 
                             auth()->user()->can('view-report-bilan');
?>
<?php if($hasReportPermission): ?>
    <div class="nav-link report-menu-header" style="color: #bdc3c7; padding: 10px 25px; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 10px;" onclick="toggleReportMenu()">
        <i class="fas fa-chart-bar me-2"></i>Rapport
    </div>
    <div class="report-menu-items">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-report-credits-octroyes')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('reports.credits-octroyes') ? 'active' : ''); ?>" href="<?php echo e(route('reports.credits-octroyes')); ?>" style="padding-left: 50px;">
                <i class="fas fa-file-invoice me-2"></i>Crédits octroyés
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-report-credits-echus')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('reports.credits-echus') ? 'active' : ''); ?>" href="<?php echo e(route('reports.credits-echus')); ?>" style="padding-left: 50px;">
                <i class="fas fa-exclamation-triangle me-2"></i>Crédits échus
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-report-global')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('reports.rapport-global') ? 'active' : ''); ?>" href="<?php echo e(route('reports.rapport-global')); ?>" style="padding-left: 50px;">
                <i class="fas fa-chart-pie me-2"></i>Rapport global
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-report-bilan')): ?>
            <a class="nav-link <?php echo e(request()->routeIs('reports.bilan-par-periode') ? 'active' : ''); ?>" href="<?php echo e(route('reports.bilan-par-periode')); ?>" style="padding-left: 50px;">
                <i class="fas fa-balance-scale me-2"></i>Bilan crédit
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\mulisys_aida\resources\views/layouts/menu.blade.php ENDPATH**/ ?>