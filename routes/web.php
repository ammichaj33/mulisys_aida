<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReceptionnisteController;
use App\Http\Controllers\ChargeCreditsController;
use App\Http\Controllers\GerantController;
use App\Http\Controllers\CaissiereController;
use App\Http\Controllers\DirecteurController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Routes d'authentification
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route racine - redirection vers le dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    
    // Routes du réceptionniste
    Route::prefix('receptionniste')->name('receptionniste.')->middleware('permission:view-dashboard-receptionniste')->group(function () {
        Route::get('/dashboard', [ReceptionnisteController::class, 'dashboard'])->name('dashboard');
        Route::get('/loans/create', [ReceptionnisteController::class, 'create'])->name('loans.create')->middleware('permission:create-loan-requests');
        Route::post('/loans', [ReceptionnisteController::class, 'store'])->name('loans.store')->middleware('permission:create-loan-requests');
        Route::get('/loans', [ReceptionnisteController::class, 'index'])->name('loans.index')->middleware('permission:view-loan-requests');
        Route::get('/loans/{id}', [ReceptionnisteController::class, 'show'])->name('loans.show')->middleware('permission:view-loan-requests');
        Route::get('/loans/{id}/edit', [ReceptionnisteController::class, 'edit'])->name('loans.edit')->middleware('permission:edit-loan-requests');
        Route::put('/loans/{id}', [ReceptionnisteController::class, 'update'])->name('loans.update')->middleware('permission:edit-loan-requests');
        Route::delete('/loans/{id}', [ReceptionnisteController::class, 'destroy'])->name('loans.destroy')->middleware('permission:delete-loan-requests');
        Route::get('/members/search', [ReceptionnisteController::class, 'searchMembers'])->name('members.search')->middleware('permission:view-members');
    });

    // Routes du chargé des crédits
    Route::prefix('charge-credits')->name('charge_credits.')->middleware('permission:view-dashboard-charge-credits')->group(function () {
        Route::get('/dashboard', [ChargeCreditsController::class, 'dashboard'])->name('dashboard');
        Route::get('/validation', [ChargeCreditsController::class, 'validation'])->name('validation')->middleware('permission:view-credit-validation');
        Route::get('/loans', [ChargeCreditsController::class, 'index'])->name('loans.index')->middleware('permission:view-loan-requests');
        Route::get('/loans/{id}', [ChargeCreditsController::class, 'show'])->name('loans.show')->middleware('permission:view-loan-requests');
        Route::get('/loans/{id}/validate', [ChargeCreditsController::class, 'validateForm'])->name('loans.validate-form')->middleware('permission:validate-credits');
        Route::post('/loans/{id}/validate', [ChargeCreditsController::class, 'validateLoan'])->name('loans.validate')->middleware('permission:validate-credits');
        Route::post('/loans/{id}/reject', [ChargeCreditsController::class, 'reject'])->name('loans.reject')->middleware('permission:reject-credits');
    });

    // Routes du gérant
    Route::prefix('gerant')->name('gerant.')->middleware('permission:view-dashboard-gerant')->group(function () {
        Route::get('/dashboard', [GerantController::class, 'dashboard'])->name('dashboard');
        Route::match(['get', 'post'], '/final-validation', [GerantController::class, 'finalValidation'])->name('final-validation')->middleware('permission:view-final-validation');
        Route::get('/loans', [GerantController::class, 'index'])->name('loans.index')->middleware('permission:view-loan-requests');
        Route::get('/loans/{id}', [GerantController::class, 'show'])->name('loans.show')->middleware('permission:view-loan-requests');
        Route::get('/loans/{id}/edit', [GerantController::class, 'edit'])->name('loans.edit')->middleware('permission:edit-loan-requests');
        Route::put('/loans/{id}', [GerantController::class, 'update'])->name('loans.update')->middleware('permission:edit-loan-requests');
        Route::get('/repayments', [GerantController::class, 'repaymentsIndex'])->name('repayments.index')->middleware('permission:view-repayments');
        Route::get('/repayments/{id}', [GerantController::class, 'showRepayment'])->name('repayments.show')->middleware('permission:view-repayments');
        Route::get('/repayments/{id}/edit', [GerantController::class, 'editRepayment'])->name('repayments.edit')->middleware('permission:edit-repayments');
        Route::put('/repayments/{id}', [GerantController::class, 'updateRepayment'])->name('repayments.update')->middleware('permission:edit-repayments');
        Route::delete('/repayments/{id}', [GerantController::class, 'destroyRepayment'])->name('repayments.destroy')->middleware('permission:delete-repayments');
    });

    // Routes de la caissière
    Route::prefix('caissiere')->name('caissiere.')->middleware('permission:view-dashboard-caissiere')->group(function () {
        Route::get('/dashboard', [CaissiereController::class, 'dashboard'])->name('dashboard');
        Route::get('/loans/{id}', [CaissiereController::class, 'show'])->name('loans.show')->middleware('permission:view-repayments');
        Route::get('/loans/{id}/schedule', [CaissiereController::class, 'schedule'])->name('loans.schedule')->middleware('permission:view-repayment-schedule');
        Route::get('/loans/{id}/early-repayment', [CaissiereController::class, 'earlyRepaymentForm'])->name('loans.early-repayment')->middleware('permission:create-repayments');
        Route::post('/loans/{id}/early-repayment', [CaissiereController::class, 'processEarlyRepayment'])->name('loans.early-repayment.process')->middleware('permission:create-repayments');
        Route::post('/loans/{id}/repayment', [CaissiereController::class, 'recordRepayment'])->name('loans.repayment')->middleware('permission:create-repayments');
        Route::get('/repayments', [CaissiereController::class, 'index'])->name('repayments.index')->middleware('permission:view-repayments');
        Route::get('/repayments/create', [CaissiereController::class, 'create'])->name('repayments.create')->middleware('permission:create-repayments');
        Route::post('/repayments', [CaissiereController::class, 'store'])->name('repayments.store')->middleware('permission:create-repayments');
        Route::get('/repayments/{id}', [CaissiereController::class, 'showRepayment'])->name('repayments.show')->middleware('permission:view-repayments');
        Route::get('/repayments/{id}/edit', [CaissiereController::class, 'edit'])->name('repayments.edit')->middleware('permission:edit-repayments');
        Route::put('/repayments/{id}', [CaissiereController::class, 'update'])->name('repayments.update')->middleware('permission:edit-repayments');
    });

    // Routes du directeur
    Route::prefix('directeur')->name('directeur.')->middleware('permission:view-dashboard-directeur')->group(function () {
        Route::get('/dashboard', [DirecteurController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports/loans', [DirecteurController::class, 'loansReport'])->name('reports.loans')->middleware('permission:view-loan-reports');
        Route::get('/reports/interests', [DirecteurController::class, 'interestsReport'])->name('reports.interests')->middleware('permission:view-interest-reports');
        Route::get('/reports/financial', [DirecteurController::class, 'financialReport'])->name('reports.financial')->middleware('permission:view-financial-reports');
    });

    // Routes des membres (accessibles à tous avec permission view-members)
    Route::prefix('members')->name('members.')->middleware('permission:view-members')->group(function () {
        Route::get('/', [MemberController::class, 'index'])->name('index')->middleware('permission:view-members');
        Route::get('/create', [MemberController::class, 'create'])->name('create')->middleware('permission:create-members');
        Route::post('/', [MemberController::class, 'store'])->name('store')->middleware('permission:create-members');
        Route::get('/{id}', [MemberController::class, 'show'])->name('show')->middleware('permission:view-members');
        Route::get('/{id}/edit', [MemberController::class, 'edit'])->name('edit')->middleware('permission:edit-members');
        Route::put('/{id}', [MemberController::class, 'update'])->name('update')->middleware('permission:edit-members');
        Route::delete('/{id}', [MemberController::class, 'destroy'])->name('destroy')->middleware('permission:delete-members');
        
        // Routes protégées pour les fichiers des membres
        Route::get('/{id}/photo', [MemberController::class, 'getPhoto'])->name('photo')->middleware('permission:view-members');
        Route::get('/{id}/idcard', [MemberController::class, 'getIdCard'])->name('idcard')->middleware('permission:view-members');
    });

    // Route PDF de l'échéancier (accessible à tous les rôles autorisés, sans préfixe spécifique)
    Route::get('/loans/{id}/schedule/pdf', [CaissiereController::class, 'generateSchedulePDF'])
        ->name('loans.schedule.pdf')
        ->middleware('permission:generate-repayment-pdf');

    // Routes des crédits
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('index')->middleware('permission:view-loan-requests');
        Route::get('/{id}', [LoanController::class, 'show'])->name('show')->middleware('permission:view-loan-requests');
        Route::get('/{id}/schedule', [LoanController::class, 'schedule'])->name('schedule')->middleware('permission:view-repayment-schedule');
        Route::get('/{id}/document', [LoanController::class, 'getDocument'])->name('document')->middleware('permission:view-loan-requests');
    });
    
    // Routes des reçus PDF
    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/repayment/{id}', [App\Http\Controllers\RepaymentReceiptController::class, 'generateReceipt'])->name('repayment')->middleware('permission:view-repayments');
        Route::get('/repayment/{id}/download', [App\Http\Controllers\RepaymentReceiptController::class, 'downloadReceipt'])->name('repayment.download')->middleware('permission:view-repayments');
    });
    
    // Routes des pénalités
    Route::prefix('penalties')->name('penalties.')->group(function () {
        Route::get('/', [App\Http\Controllers\PenaltyController::class, 'index'])->name('index')->middleware('permission:view-penalties');
        Route::get('/{id}', [App\Http\Controllers\PenaltyController::class, 'show'])->name('show')->middleware('permission:view-penalties');
        Route::post('/{id}/pay', [App\Http\Controllers\PenaltyController::class, 'pay'])->name('pay')->middleware('permission:pay-penalties');
        Route::post('/calculate-all', [App\Http\Controllers\PenaltyController::class, 'calculateAll'])->name('calculate-all')->middleware('permission:create-penalties');
        Route::post('/loan/{id}/calculate', [App\Http\Controllers\PenaltyController::class, 'calculateForLoan'])->name('calculate-loan')->middleware('permission:create-penalties');
    });
    
    // Routes des rapports (accessibles à tous avec permissions spécifiques)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/credits-octroyes', [ReportController::class, 'creditsOctroyes'])->name('credits-octroyes')->middleware('permission:view-report-credits-octroyes');
        Route::get('/credits-echus', [ReportController::class, 'creditsEchus'])->name('credits-echus')->middleware('permission:view-report-credits-echus');
        Route::get('/rapport-global', [ReportController::class, 'rapportGlobal'])->name('rapport-global')->middleware('permission:view-report-global');
        Route::get('/bilan-par-periode', [ReportController::class, 'bilanParPeriode'])->name('bilan-par-periode')->middleware('permission:view-report-bilan');
    });
    
    // Routes du module Cashflow (Trésorerie)
    Route::prefix('cashflow')->name('cashflow.')->group(function () {
        // Routes spécifiques AVANT les routes avec paramètres dynamiques
        // Catégories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\Cashflow\CashflowCategoryController::class, 'index'])->name('index')->middleware('permission:manage-cashflow-categories');
            Route::get('/create', [App\Http\Controllers\Cashflow\CashflowCategoryController::class, 'create'])->name('create')->middleware('permission:manage-cashflow-categories');
            Route::post('/', [App\Http\Controllers\Cashflow\CashflowCategoryController::class, 'store'])->name('store')->middleware('permission:manage-cashflow-categories');
            Route::get('/{id}/edit', [App\Http\Controllers\Cashflow\CashflowCategoryController::class, 'edit'])->name('edit')->middleware('permission:manage-cashflow-categories');
            Route::put('/{id}', [App\Http\Controllers\Cashflow\CashflowCategoryController::class, 'update'])->name('update')->middleware('permission:manage-cashflow-categories');
            Route::delete('/{id}', [App\Http\Controllers\Cashflow\CashflowCategoryController::class, 'destroy'])->name('destroy')->middleware('permission:manage-cashflow-categories');
        });
        
        // Comptes
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [App\Http\Controllers\Cashflow\CashflowAccountController::class, 'index'])->name('index')->middleware('permission:manage-cashflow-accounts');
            Route::get('/create', [App\Http\Controllers\Cashflow\CashflowAccountController::class, 'create'])->name('create')->middleware('permission:manage-cashflow-accounts');
            Route::post('/', [App\Http\Controllers\Cashflow\CashflowAccountController::class, 'store'])->name('store')->middleware('permission:manage-cashflow-accounts');
            Route::get('/{id}', [App\Http\Controllers\Cashflow\CashflowAccountController::class, 'show'])->name('show')->middleware('permission:view-cashflow');
            Route::get('/{id}/edit', [App\Http\Controllers\Cashflow\CashflowAccountController::class, 'edit'])->name('edit')->middleware('permission:manage-cashflow-accounts');
            Route::put('/{id}', [App\Http\Controllers\Cashflow\CashflowAccountController::class, 'update'])->name('update')->middleware('permission:manage-cashflow-accounts');
            Route::delete('/{id}', [App\Http\Controllers\Cashflow\CashflowAccountController::class, 'destroy'])->name('destroy')->middleware('permission:manage-cashflow-accounts');
            Route::post('/{id}/recalculate', [App\Http\Controllers\Cashflow\CashflowAccountController::class, 'recalculateBalance'])->name('recalculate')->middleware('permission:manage-cashflow-accounts');
        });
        
        // Rapports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/by-period', [App\Http\Controllers\Cashflow\CashflowReportController::class, 'cashflowByPeriod'])->name('by-period')->middleware('permission:view-cashflow-reports');
            Route::get('/by-category', [App\Http\Controllers\Cashflow\CashflowReportController::class, 'cashflowByCategory'])->name('by-category')->middleware('permission:view-cashflow-reports');
            Route::get('/treasury', [App\Http\Controllers\Cashflow\CashflowReportController::class, 'treasuryReport'])->name('treasury')->middleware('permission:view-cashflow-reports');
        });
        
        // Transactions (routes avec paramètres dynamiques en dernier)
        Route::get('/', [App\Http\Controllers\Cashflow\CashflowController::class, 'index'])->name('index')->middleware('permission:view-cashflow');
        Route::get('/create', [App\Http\Controllers\Cashflow\CashflowController::class, 'create'])->name('create')->middleware('permission:create-cashflow');
        Route::post('/', [App\Http\Controllers\Cashflow\CashflowController::class, 'store'])->name('store')->middleware('permission:create-cashflow');
        Route::get('/{id}', [App\Http\Controllers\Cashflow\CashflowController::class, 'show'])->name('show')->middleware('permission:view-cashflow');
        Route::get('/{id}/edit', [App\Http\Controllers\Cashflow\CashflowController::class, 'edit'])->name('edit')->middleware('permission:edit-cashflow');
        Route::put('/{id}', [App\Http\Controllers\Cashflow\CashflowController::class, 'update'])->name('update')->middleware('permission:edit-cashflow');
        Route::delete('/{id}', [App\Http\Controllers\Cashflow\CashflowController::class, 'destroy'])->name('destroy')->middleware('permission:delete-cashflow');
        Route::post('/{id}/confirm', [App\Http\Controllers\Cashflow\CashflowController::class, 'confirm'])->name('confirm')->middleware('permission:confirm-cashflow');
        Route::post('/{id}/cancel', [App\Http\Controllers\Cashflow\CashflowController::class, 'cancel'])->name('cancel')->middleware('permission:edit-cashflow');
    });
    
    // Routes Admin (Gestion des utilisateurs, rôles et permissions)
    Route::prefix('admin')->name('admin.')->middleware('permission:manage-users')->group(function () {
        // Utilisateurs
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\AdminUserController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/assign-roles', [App\Http\Controllers\Admin\AdminUserController::class, 'assignRoles'])->name('assign-roles');
            Route::post('/{id}/assign-permissions', [App\Http\Controllers\Admin\AdminUserController::class, 'assignPermissions'])->name('assign-permissions');
        });
        
        // Rôles
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminRolePermissionController::class, 'rolesIndex'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\AdminRolePermissionController::class, 'rolesCreate'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\AdminRolePermissionController::class, 'rolesStore'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Admin\AdminRolePermissionController::class, 'rolesShow'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\AdminRolePermissionController::class, 'rolesEdit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\AdminRolePermissionController::class, 'rolesUpdate'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\AdminRolePermissionController::class, 'rolesDestroy'])->name('destroy');
        });
        
        // Permissions
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminRolePermissionController::class, 'permissionsIndex'])->name('index');
        });
    });
});