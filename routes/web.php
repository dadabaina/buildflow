<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\DosageController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AmendmentController;
use App\Http\Controllers\ProgressBillingController;
use App\Http\Controllers\SiteReportController;
use App\Http\Controllers\ReceptionReportController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Application (auth + tenant) ───────────────────────────────────────────────
Route::middleware(['auth', 'tenant'])->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    // Contacts
    Route::resource('clients', ClientController::class);
    Route::patch('clients/{id}/restore', [ClientController::class, 'restore'])->name('clients.restore');
    Route::resource('suppliers', SupplierController::class);
    Route::resource('employees', EmployeeController::class);
    Route::patch('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');

    // Projets & sous-ressources
    Route::resource('projects', ProjectController::class);
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])
        ->name('projects.status');
    Route::post('projects/{project}/requirements', [ProjectController::class, 'storeRequirement'])
        ->name('projects.requirements.store');
    Route::delete('projects/{project}/requirements/{requirement}', [ProjectController::class, 'destroyRequirement'])
        ->name('projects.requirements.destroy');
    Route::post('projects/{project}/employees', [ProjectController::class, 'syncEmployees'])
        ->name('projects.employees.sync');
    Route::delete('projects/{project}/employees/{employee}', [ProjectController::class, 'detachEmployee'])
        ->name('projects.employees.detach');
    Route::post('projects/{project}/thresholds', [ProjectController::class, 'updateThreshold'])
        ->name('projects.thresholds.update');
    Route::post('projects/{project}/equipments', [ProjectController::class, 'assignEquipment'])
        ->name('projects.equipments.assign');
    Route::delete('projects/{project}/equipments/{assignment}', [ProjectController::class, 'detachEquipment'])
        ->name('projects.equipments.detach');

    // Dépenses
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('expenses', ExpenseController::class);
    Route::patch('expenses/{expense}/validate', [ExpenseController::class, 'validate'])
        ->name('expenses.validate');
    Route::patch('expenses/{expense}/reject', [ExpenseController::class, 'reject'])
        ->name('expenses.reject');

    // Devis
    Route::resource('quotes', QuoteController::class);
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'exportPdf'])
        ->name('quotes.pdf');
    Route::post('quotes/{quote}/send', [QuoteController::class, 'send'])
        ->name('quotes.send');
    Route::post('quotes/{quote}/accept', [QuoteController::class, 'accept'])
        ->name('quotes.accept');
    Route::post('quotes/{quote}/tasks', [QuoteController::class, 'generateTasks'])
        ->name('quotes.tasks');
    Route::post('quotes/{quote}/refuse', [QuoteController::class, 'refuse'])
        ->name('quotes.refuse');
    Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])
        ->name('quotes.duplicate');
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convertToInvoice'])
        ->name('quotes.convert');
    Route::post('quotes/{quote}/items', [QuoteController::class, 'addItem'])
        ->name('quotes.items.add');
    Route::patch('quotes/{quote}/items/{item}', [QuoteController::class, 'updateItem'])
        ->name('quotes.items.update');
    Route::delete('quotes/{quote}/items/{item}', [QuoteController::class, 'removeItem'])
        ->name('quotes.items.remove');
    Route::post('quotes/{quote}/sections', [QuoteController::class, 'addSection'])
        ->name('quotes.sections.add');
    Route::delete('quotes/{quote}/sections/{section}', [QuoteController::class, 'removeSection'])
        ->name('quotes.sections.remove');

    // Factures
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'markSent'])
        ->name('invoices.send');
    Route::post('invoices/{invoice}/items', [InvoiceController::class, 'addItem'])
        ->name('invoices.items.add');
    Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class, 'removeItem'])
        ->name('invoices.items.remove');

    // Paiements
    Route::resource('payments', PaymentController::class);

    // Paramètres
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company');
    Route::get('/settings/regions', [SettingsController::class, 'regionsIndex'])->name('settings.regions.index');
    Route::post('/settings/regions', [SettingsController::class, 'storeRegion'])->name('settings.regions.store');
    Route::delete('/settings/regions/{region}', [SettingsController::class, 'destroyRegion'])->name('settings.regions.destroy');
    Route::get('/settings/job-types', [SettingsController::class, 'jobTypesIndex'])->name('settings.job_types.index');
    Route::post('/settings/job-types', [SettingsController::class, 'storeJobType'])->name('settings.job_types.store');
    Route::delete('/settings/job-types/{jobType}', [SettingsController::class, 'destroyJobType'])->name('settings.job_types.destroy');
    Route::post('/settings/job-categories', [SettingsController::class, 'storeJobCategory'])->name('settings.job_categories.store');
    Route::delete('/settings/job-categories/{jobCategory}', [SettingsController::class, 'destroyJobCategory'])->name('settings.job_categories.destroy');
    Route::get('/settings/unit-types', [SettingsController::class, 'unitTypesIndex'])->name('settings.unit_types.index');
    Route::post('/settings/unit-types', [SettingsController::class, 'storeUnitType'])->name('settings.unit_types.store');
    Route::delete('/settings/unit-types/{unitType}', [SettingsController::class, 'destroyUnitType'])->name('settings.unit_types.destroy');
    Route::get('/settings/expense-categories', [SettingsController::class, 'expenseCategoriesIndex'])->name('settings.expense_categories.index');
    Route::post('/settings/expense-categories', [SettingsController::class, 'storeExpenseCategory'])->name('settings.expense_categories.store');
    Route::delete('/settings/expense-categories/{expenseCategory}', [SettingsController::class, 'destroyExpenseCategory'])->name('settings.expense_categories.destroy');
    Route::get('/settings/salary-rates', [SettingsController::class, 'salaryRatesIndex'])->name('settings.salary_rates.index');
    Route::post('/settings/salary-rates', [SettingsController::class, 'storeSalaryRate'])->name('settings.salary_rates.store');
    Route::delete('/settings/salary-rates/{salaryRate}', [SettingsController::class, 'destroySalaryRate'])->name('settings.salary_rates.destroy');
    Route::get('/settings/material-categories', [SettingsController::class, 'materialCategoriesIndex'])->name('settings.material_categories.index');
    Route::post('/settings/material-categories', [SettingsController::class, 'storeMaterialCategory'])->name('settings.material_categories.store');
    Route::delete('/settings/material-categories/{materialCategory}', [SettingsController::class, 'destroyMaterialCategory'])->name('settings.material_categories.destroy');

    // Utilisateurs
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // Matériaux (bibliothèque de prix DBE)
    Route::get('materials/export', [MaterialController::class, 'exportCsv'])->name('materials.export');
    Route::get('materials/search', [MaterialController::class, 'search'])->name('materials.search');
    Route::get('/materials/{material}/stock-breakdown', [MaterialController::class, 'stockBreakdown'])->name('materials.stock_breakdown');
    Route::resource('materials', MaterialController::class);
    Route::post('materials/{material}/prices', [MaterialController::class, 'storePrice'])->name('materials.prices.store');
    Route::delete('materials/{material}/prices/{price}', [MaterialController::class, 'destroyPrice'])->name('materials.prices.destroy');

    // Modèles de dosage (DBE)
    Route::post('dosage/calculate', [DosageController::class, 'calculate'])->name('dosage.calculate');
    Route::resource('dosage', DosageController::class);
    Route::post('dosage/{dosage}/items', [DosageController::class, 'storeItem'])->name('dosage.items.store');
    Route::delete('dosage/{dosage}/items/{item}', [DosageController::class, 'destroyItem'])->name('dosage.items.destroy');

    // ── Wave 6 : Bons de commande ────────────────────────────────────────────
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::patch('purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.status');
    Route::post('purchase-orders/{purchaseOrder}/convert-expense', [PurchaseOrderController::class, 'convertToExpense'])->name('purchase-orders.convert-expense');

    // ── Wave 7 : Tâches ──────────────────────────────────────────────────────
    Route::get('tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::patch('tasks/{task}/checklist', [TaskController::class, 'updateChecklist'])->name('tasks.checklist');
    Route::post('tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::resource('tasks', TaskController::class);

    // ── Wave 8 : Pointage ────────────────────────────────────────────────────
    Route::get('attendances/recap', [AttendanceController::class, 'recap'])->name('attendances.recap');
    Route::get('attendances/recap/export', [AttendanceController::class, 'exportCsv'])->name('attendances.recap.export');
    Route::resource('attendances', AttendanceController::class)->except(['show']);

    // ── Wave 9 : Documents ───────────────────────────────────────────────────
    Route::resource('documents', DocumentController::class)->except(['edit', 'update']);

    // ── Wave 10 : Avenants ──────────────────────────────────────────────────
    Route::resource('amendments', AmendmentController::class);
    Route::post('amendments/{amendment}/send', [AmendmentController::class, 'send'])->name('amendments.send');
    Route::post('amendments/{amendment}/accept', [AmendmentController::class, 'accept'])->name('amendments.accept');
    Route::post('amendments/{amendment}/refuse', [AmendmentController::class, 'refuse'])->name('amendments.refuse');

    // ── Wave 10 : Situations de travaux ─────────────────────────────────────
    Route::resource('progress-billings', ProgressBillingController::class);
    Route::post('progress-billings/{progressBilling}/send', [ProgressBillingController::class, 'send'])->name('progress-billings.send');
    Route::post('progress-billings/{progressBilling}/validate', [ProgressBillingController::class, 'validateBilling'])->name('progress-billings.validate');
    Route::post('progress-billings/{progressBilling}/invoice', [ProgressBillingController::class, 'generateInvoice'])->name('progress-billings.invoice');

    // ── Wave 11 : Compte-rendus & PV Réception ───────────────────────────────
    Route::get('site-reports/export/{siteReport}', [SiteReportController::class, 'exportPdf'])->name('site-reports.export');
    Route::post('site-reports/{siteReport}/finalize', [SiteReportController::class, 'finalize'])->name('site-reports.finalize');
    Route::post('site-reports/{siteReport}/items', [SiteReportController::class, 'storeItem'])->name('site-reports.items.store');
    Route::patch('site-reports/{siteReport}/items/{item}', [SiteReportController::class, 'updateItem'])->name('site-reports.items.update');
    Route::delete('site-reports/{siteReport}/items/{item}', [SiteReportController::class, 'destroyItem'])->name('site-reports.items.destroy');
    Route::resource('site-reports', SiteReportController::class);

    Route::post('reception-reports/{receptionReport}/accept', [ReceptionReportController::class, 'accept'])->name('reception-reports.accept');
    Route::post('reception-reports/{receptionReport}/release-rg', [ReceptionReportController::class, 'releaseRg'])->name('reception-reports.release-rg');
    Route::get('reception-reports/{receptionReport}/export', [ReceptionReportController::class, 'exportPdf'])->name('reception-reports.export');
    Route::resource('reception-reports', ReceptionReportController::class);

    // ── Wave 12 : Matériels & Stocks ─────────────────────────────────────────
    Route::post('equipments/{equipment}/maintenances', [EquipmentController::class, 'storeMaintenance'])->name('equipments.maintenances.store');
    Route::delete('equipments/{equipment}/maintenances/{maintenance}', [EquipmentController::class, 'destroyMaintenance'])->name('equipments.maintenances.destroy');
    Route::resource('equipments', EquipmentController::class);

    Route::resource('warehouses', WarehouseController::class)->except(['show']);

    Route::get('stock/dashboard', [StockMovementController::class, 'dashboard'])->name('stock.dashboard');
    Route::get('stock/export', [StockMovementController::class, 'export'])->name('stock.export');
    Route::resource('stock-movements', StockMovementController::class)->except(['edit', 'update']);

    // ── Wave 13 : Notifications ──────────────────────────────────────────────
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // ── Wave 14 : Rapports ───────────────────────────────────────────────────
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('/reports/projects', [ReportController::class, 'projects'])->name('reports.projects');
    Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/planned-vs-real', [ReportController::class, 'plannedVsReal'])->name('reports.planned-vs-real');

});

// ── Routes publiques ──────────────────────────────────────────────────────────
// Validation client de devis (lien public par token)
Route::get('/devis/{token}/valider', [QuoteController::class, 'publicValidation'])
    ->name('quotes.public');
Route::post('/devis/{token}/valider', [QuoteController::class, 'publicValidate'])
    ->name('quotes.public.validate');

