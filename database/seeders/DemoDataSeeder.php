<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\JobType;
use App\Models\Region;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Créer la société de démonstration
        $company = Company::firstOrCreate(
            ['slug' => 'demo-btp'],
            [
                'name' => 'Démo BTP',
                'email' => 'demo@buildflow.mg',
                'phone' => '+261 34 00 000 00',
                'address' => 'Antananarivo, Madagascar',
                'currency' => 'MGA',
                'tva_rate' => 20.00,
                'rg_rate' => 5.00,
                'fg_rate' => 10.00,
                'marge_rate' => 15.00,
                'aleas_rate' => 5.00,
                'quote_prefix' => 'DEV',
                'invoice_prefix' => 'FAC',
                'plan' => 'pro',
                'plan_expires_at' => now()->addYear(),
                'is_active' => true,
                'settings' => [],
            ]
        );

        // Créer l'utilisateur admin de la démo
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@demo.mg'],
            [
                'name' => 'Admin Démo',
                'password' => Hash::make('123456'),
                'company_id' => $company->id,
                'job_title' => 'Directeur',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('admin');

        // Régions de Madagascar
        $regions = [
            ['name' => 'Analamanga', 'code' => 'AN'],
            ['name' => 'Vakinankaratra', 'code' => 'VA'],
            ['name' => 'Itasy', 'code' => 'IT'],
            ['name' => 'Bongolava', 'code' => 'BO'],
            ['name' => 'Matsiatra Ambony', 'code' => 'MA'],
            ['name' => 'Amoron\'i Mania', 'code' => 'AM'],
            ['name' => 'Vatovavy', 'code' => 'VT'],
            ['name' => 'Fitovinany', 'code' => 'FI'],
            ['name' => 'Atsimo-Atsinanana', 'code' => 'AA'],
            ['name' => 'Atsinanana', 'code' => 'AT'],
            ['name' => 'Analanjirofo', 'code' => 'AJ'],
            ['name' => 'Alaotra-Mangoro', 'code' => 'AL'],
            ['name' => 'Boeny', 'code' => 'BN'],
            ['name' => 'Sofia', 'code' => 'SO'],
            ['name' => 'Betsiboka', 'code' => 'BT'],
            ['name' => 'Melaky', 'code' => 'ME'],
            ['name' => 'Atsimo-Andrefana', 'code' => 'AR'],
            ['name' => 'Androy', 'code' => 'AD'],
            ['name' => 'Anosy', 'code' => 'AN2'],
            ['name' => 'Menabe', 'code' => 'MN'],
            ['name' => 'Diana', 'code' => 'DI'],
            ['name' => 'Sava', 'code' => 'SA'],
        ];

        foreach ($regions as $r) {
            Region::firstOrCreate(
                ['company_id' => $company->id, 'code' => $r['code']],
                ['name' => $r['name'], 'is_active' => true]
            );
        }

        // Types de corps de métier
        $jobTypes = [
            ['name' => 'Maçonnerie', 'code' => 'MAC'],
            ['name' => 'Ferraillage', 'code' => 'FER'],
            ['name' => 'Plomberie', 'code' => 'PLB'],
            ['name' => 'Électricité', 'code' => 'ELC'],
            ['name' => 'Peinture', 'code' => 'PNT'],
            ['name' => 'Menuiserie bois', 'code' => 'MNB'],
            ['name' => 'Menuiserie aluminium', 'code' => 'MNA'],
            ['name' => 'Carrelage', 'code' => 'CAR'],
            ['name' => 'Charpente', 'code' => 'CHP'],
            ['name' => 'Couverture', 'code' => 'CUV'],
            ['name' => 'Terrassement', 'code' => 'TER'],
            ['name' => 'Conducteur de travaux', 'code' => 'CDT'],
            ['name' => 'Topographe', 'code' => 'TOP'],
            ['name' => 'Chef de chantier', 'code' => 'CDC'],
            ['name' => 'Manœuvre', 'code' => 'MAN'],
        ];

        foreach ($jobTypes as $jt) {
            JobType::firstOrCreate(
                ['company_id' => $company->id, 'code' => $jt['code']],
                ['name' => $jt['name'], 'is_active' => true]
            );
        }

        // Unités de mesure
        $units = [
            ['name' => 'Mètre', 'symbol' => 'm'],
            ['name' => 'Mètre carré', 'symbol' => 'm²'],
            ['name' => 'Mètre cube', 'symbol' => 'm³'],
            ['name' => 'Kilogramme', 'symbol' => 'kg'],
            ['name' => 'Tonne', 'symbol' => 't'],
            ['name' => 'Pièce', 'symbol' => 'pce'],
            ['name' => 'Forfait', 'symbol' => 'fft'],
            ['name' => 'Jour', 'symbol' => 'j'],
            ['name' => 'Heure', 'symbol' => 'h'],
            ['name' => 'Lot', 'symbol' => 'lot'],
            ['name' => 'Sac', 'symbol' => 'sac'],
            ['name' => 'Litre', 'symbol' => 'L'],
        ];

        foreach ($units as $u) {
            UnitType::firstOrCreate(
                ['company_id' => $company->id, 'symbol' => $u['symbol']],
                ['name' => $u['name'], 'is_active' => true]
            );
        }

        // Catégories de dépenses
        $expenseCategories = [
            ['name' => 'Matériaux de construction', 'color' => '#e74c3c', 'icon' => 'bi-bricks'],
            ['name' => 'Main-d\'œuvre journalière', 'color' => '#3498db', 'icon' => 'bi-person-hard-hat'],
            ['name' => 'Location matériel', 'color' => '#f39c12', 'icon' => 'bi-truck'],
            ['name' => 'Carburant & transport', 'color' => '#e67e22', 'icon' => 'bi-fuel-pump'],
            ['name' => 'Sous-traitance', 'color' => '#9b59b6', 'icon' => 'bi-people'],
            ['name' => 'Fournitures de chantier', 'color' => '#1abc9c', 'icon' => 'bi-tools'],
            ['name' => 'Frais divers', 'color' => '#95a5a6', 'icon' => 'bi-three-dots'],
        ];

        foreach ($expenseCategories as $ec) {
            ExpenseCategory::firstOrCreate(
                ['company_id' => $company->id, 'name' => $ec['name']],
                ['color' => $ec['color'], 'icon' => $ec['icon'], 'is_active' => true]
            );
        }
    }
}
