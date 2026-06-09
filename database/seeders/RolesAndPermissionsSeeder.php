<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Clients
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            // Fournisseurs
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            // Employés
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            // Projets
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'projects.change_status',
            // Dépenses
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'expenses.validate', 'expenses.reject',
            // Devis
            'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.delete', 'quotes.send', 'quotes.accept',
            // Factures
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.send',
            // Paiements
            'payments.view', 'payments.create', 'payments.delete',
            // Bons de commande
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.delete',
            // Tâches
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            // Pointage
            'attendances.view', 'attendances.create', 'attendances.edit', 'attendances.delete',
            // Matériaux
            'materials.view', 'materials.create', 'materials.edit', 'materials.delete',
            // Dosage DBE
            'dosage.view', 'dosage.create', 'dosage.edit', 'dosage.delete',
            // Documents
            'documents.view', 'documents.create', 'documents.delete',
            // Avenants
            'amendments.view', 'amendments.create', 'amendments.edit', 'amendments.delete',
            // Situations de travaux
            'progress_billings.view', 'progress_billings.create', 'progress_billings.edit', 'progress_billings.delete',
            // Compte-rendus de chantier (Wave 11)
            'site_reports.view', 'site_reports.create', 'site_reports.edit', 'site_reports.delete',
            // PV de réception (Wave 11)
            'reception_reports.view', 'reception_reports.create', 'reception_reports.edit', 'reception_reports.delete',
            // Matériels & équipements (Wave 12)
            'equipments.view', 'equipments.create', 'equipments.edit', 'equipments.delete',
            // Dépôts & stocks (Wave 12)
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
            'stock.view', 'stock.create', 'stock.delete',
            // Rapports
            'reports.view',
            // Paramètres
            'settings.view', 'settings.edit',
            // Utilisateurs
            'users.view', 'users.create', 'users.edit', 'users.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Super Admin (pas de company_id requis)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin société : tous droits dans sa société
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::whereNot('name', 'like', 'settings.%')->get());
        $admin->givePermissionTo(['settings.view', 'settings.edit']);

        // Manager : gère projets/dépenses/devis/factures, pas utilisateurs ni settings
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'clients.view', 'clients.create', 'clients.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'employees.view', 'employees.create', 'employees.edit',
            'projects.view', 'projects.create', 'projects.edit', 'projects.change_status',
            'expenses.view', 'expenses.create', 'expenses.validate', 'expenses.reject',
            'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.send', 'quotes.accept',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.send',
            'payments.view', 'payments.create',
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit',
            'tasks.view', 'tasks.create', 'tasks.edit',
            'attendances.view', 'attendances.create', 'attendances.edit',
            'documents.view', 'documents.create',
            'materials.view', 'materials.create', 'materials.edit',
            'dosage.view', 'dosage.create', 'dosage.edit',
            'amendments.view', 'amendments.create', 'amendments.edit',
            'progress_billings.view', 'progress_billings.create', 'progress_billings.edit',
            'site_reports.view', 'site_reports.create', 'site_reports.edit',
            'reception_reports.view', 'reception_reports.create', 'reception_reports.edit',
            'equipments.view', 'equipments.create', 'equipments.edit',
            'warehouses.view', 'warehouses.create',
            'stock.view', 'stock.create',
            'reports.view',
        ]);

        // Comptable : focus finances
        $accountant = Role::firstOrCreate(['name' => 'comptable', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'clients.view',
            'projects.view',
            'expenses.view', 'expenses.validate', 'expenses.reject',
            'quotes.view',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.send',
            'payments.view', 'payments.create',
            'reports.view',
        ]);

        // Opérateur : saisie terrain
        $operator = Role::firstOrCreate(['name' => 'operateur', 'guard_name' => 'web']);
        $operator->syncPermissions([
            'projects.view',
            'expenses.view', 'expenses.create',
        ]);
    }
}
