<?php

namespace App\Http\Controllers;

use App\Mail\NotificationDigestMail;
use App\Models\CompanyMailSettings;
use App\Models\ExpenseCategory;
use App\Models\JobCategory;
use App\Models\JobType;
use App\Models\MaterialCategory;
use App\Models\NotificationEmailSetting;
use App\Models\Region;
use App\Models\SalaryRate;
use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;
        return view('settings.index', compact('company'));
    }

    public function regionsIndex()
    {
        $regions = Auth::user()->company->regions()->orderBy('name')->get();
        return view('settings.regions', compact('regions'));
    }

    public function jobTypesIndex(Request $request)
    {
        $company       = Auth::user()->company;
        $categoryId    = $request->query('category_id');

        $jobTypesQuery = $company->jobTypes()->with('category')->withCount('employees')->orderBy('name');
        if ($categoryId) {
            $jobTypesQuery->where('job_category_id', $categoryId);
        }
        $jobTypes = $jobTypesQuery->paginate(15)->withQueryString();

        $jobCategories = $company->jobCategories()->withCount('jobTypes')->orderBy('name')->get();

        return view('settings.job-types', compact('jobTypes', 'jobCategories', 'categoryId'));
    }

    public function unitTypesIndex()
    {
        $unitTypes = Auth::user()->company->unitTypes()->orderBy('name')->get();
        return view('settings.unit-types', compact('unitTypes'));
    }

    public function expenseCategoriesIndex()
    {
        $categories = Auth::user()->company->expenseCategories()->orderBy('name')->get();
        return view('settings.expense-categories', compact('categories'));
    }

    public function salaryRatesIndex()
    {
        $company     = Auth::user()->company;
        $salaryRates = $company->salaryRates()->with(['jobType', 'region'])->orderByDesc('effective_date')->get();
        $jobTypes    = $company->jobTypes()->orderBy('name')->get();
        $regions     = $company->regions()->orderBy('name')->get();
        return view('settings.salary-rates', compact('salaryRates', 'jobTypes', 'regions'));
    }

    public function materialCategoriesIndex()
    {
        $materialCategories = Auth::user()->company->materialCategories()->orderBy('name')->get();
        return view('settings.material-categories', compact('materialCategories'));
    }

    // -- Notifications par email --
    public function notificationEmailsIndex()
    {
        $company = Auth::user()->company;
        $existing = $company->notificationEmailSettings()->get()->keyBy('notification_type');

        $types = collect(NotificationEmailSetting::TYPES)->map(fn ($label, $type) => [
            'type'         => $type,
            'label'        => $label,
            'emails'       => $existing[$type]->emails ?? [],
            'setting_id'   => $existing[$type]->id ?? null,
        ])->values();

        $mailSettings = $company->mailSettings;

        return view('settings.notification-emails', compact('types', 'mailSettings'));
    }

    // -- Configuration SMTP de la société --
    public function updateMailSettings(Request $request)
    {
        $data = $request->validate([
            'is_enabled'   => ['nullable', 'boolean'],
            'host'         => ['required_if:is_enabled,1', 'nullable', 'string', 'max:255'],
            'port'         => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username'     => ['nullable', 'string', 'max:255'],
            'password'     => ['nullable', 'string', 'max:255'],
            'encryption'   => ['nullable', 'in:tls,ssl,'],
            'from_address' => ['nullable', 'email', 'max:255'],
            'from_name'    => ['nullable', 'string', 'max:255'],
        ]);

        $company = Auth::user()->company;

        // Requête directe (pas $company->mailSettings) : la relation hasOne peut avoir été
        // mise en cache comme "absente" sur cette instance avant que la ligne existe.
        $settings = CompanyMailSettings::where('company_id', $company->id)->first()
            ?? new CompanyMailSettings(['company_id' => $company->id]);

        $settings->fill([
            'is_enabled'   => $request->boolean('is_enabled'),
            'host'         => $data['host'] ?? null,
            'port'         => $data['port'] ?? null,
            'username'     => $data['username'] ?? null,
            'encryption'   => $data['encryption'] ?? null,
            'from_address' => $data['from_address'] ?? null,
            'from_name'    => $data['from_name'] ?? null,
        ]);

        // On ne remplace le mot de passe que si un nouveau a été saisi :
        // le champ est laissé vide côté formulaire pour ne pas exposer la valeur existante.
        if (!empty($data['password'])) {
            $settings->password = $data['password'];
        }

        $settings->save();

        return redirect()->route('settings.notification_emails.index')
            ->with('success', 'Configuration SMTP mise à jour.');
    }

    public function testMailSettings(Request $request)
    {
        $company = Auth::user()->company;
        $settings = CompanyMailSettings::where('company_id', $company->id)->first();

        if (!$settings || !$settings->host) {
            return back()->with('error', 'Enregistrez d\'abord une configuration SMTP avant de la tester.');
        }

        $mailable = new NotificationDigestMail(
            $company,
            'Test de configuration SMTP',
            new Collection([[
                'title'   => 'Email de test',
                'message' => 'Si vous recevez cet email, votre configuration SMTP fonctionne correctement.',
                'url'     => route('settings.notification_emails.index'),
            ]]),
            Carbon::now(),
        );

        try {
            // Le test doit pouvoir s'exécuter même si "activer" n'est pas encore coché,
            // pour permettre de vérifier la config avant de l'activer réellement.
            $name = 'company_' . $company->id . '_test';
            Config::set("mail.mailers.{$name}", [
                'transport'  => 'smtp',
                'host'       => $settings->host,
                'port'       => $settings->port ?: 587,
                'encryption' => $settings->encryption ?: null,
                'username'   => $settings->username,
                'password'   => $settings->password,
                'timeout'    => 10,
            ]);

            Mail::mailer($name)->to(Auth::user()->email)->send($mailable);
        } catch (\Throwable $e) {
            return back()->with('error', 'Échec de l\'envoi : ' . $e->getMessage());
        }

        return back()->with('success', 'Email de test envoyé à ' . Auth::user()->email . '.');
    }

    public function storeNotificationEmail(Request $request)
    {
        $request->validate([
            'notification_type' => ['required', 'in:' . implode(',', array_keys(NotificationEmailSetting::TYPES))],
            'email'              => ['required', 'email', 'max:255'],
        ]);

        $company = Auth::user()->company;
        $setting = $company->notificationEmailSettings()->firstOrNew(['notification_type' => $request->notification_type]);
        $emails  = $setting->emails ?? [];

        if (!in_array($request->email, $emails, true)) {
            $emails[] = $request->email;
        }
        $setting->emails = $emails;
        $setting->save();

        return back()->with('success', 'Adresse email ajoutée.');
    }

    public function destroyNotificationEmail(Request $request, NotificationEmailSetting $notificationEmailSetting)
    {
        abort_if($notificationEmailSetting->company_id !== Auth::user()->company_id, 403);
        $request->validate(['email' => ['required', 'email']]);

        $notificationEmailSetting->emails = array_values(array_diff($notificationEmailSetting->emails ?? [], [$request->email]));
        $notificationEmailSetting->save();

        return back()->with('success', 'Adresse email retirée.');
    }

    public function updateCompany(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'nullable|email|max:255',
            'phone'               => 'nullable|string|max:50',
            'address'             => 'nullable|string',
            'nif'                 => 'nullable|string|max:50',
            'stat'                => 'nullable|string|max:50',
            'rcs'                 => 'nullable|string|max:50',
            'quote_prefix'        => 'nullable|string|max:10',
            'invoice_prefix'      => 'nullable|string|max:10',
            'credit_note_prefix'  => 'nullable|string|max:10',
            'purchase_order_prefix' => 'nullable|string|max:10',
            'project_prefix'      => 'nullable|string|max:10',
            'tva_rate'            => 'nullable|numeric|min:0|max:100',
        ]);

        $company = Auth::user()->company;
        $company->update($request->only([
            'name', 'email', 'phone', 'address', 'website', 'nif', 'stat', 'rcs',
            'quote_prefix', 'invoice_prefix', 'credit_note_prefix',
            'purchase_order_prefix', 'project_prefix', 'tva_rate',
        ]));

        return back()->with('success', 'Paramètres mis à jour.');
    }

    // -- Regions --
    public function storeRegion(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        Auth::user()->company->regions()->create(['name' => $request->name]);
        return redirect()->route('settings.regions.index')->with('success', 'Région ajoutée.');
    }

    public function destroyRegion(Region $region)
    {
        abort_if($region->company_id !== Auth::user()->company_id, 403);
        $region->delete();
        return redirect()->route('settings.regions.index')->with('success', 'Région supprimée.');
    }

    // -- Job Types --
    public function storeJobType(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'metiers'         => 'nullable|string',
            'job_category_id' => 'nullable|exists:job_categories,id',
        ]);
        Auth::user()->company->jobTypes()->create($request->only(['name', 'metiers', 'job_category_id']));
        return redirect()->route('settings.job_types.index')->with('success', 'Poste ajouté.');
    }

    public function destroyJobType(JobType $jobType)
    {
        abort_if($jobType->company_id !== Auth::user()->company_id, 403);
        $jobType->delete();
        return redirect()->route('settings.job_types.index')->with('success', 'Poste supprimé.');
    }

    // -- Job Categories --
    public function storeJobCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        Auth::user()->company->jobCategories()->create(['name' => $request->name]);
        return redirect()->route('settings.job_types.index')->with('success', 'Catégorie de poste ajoutée.');
    }

    public function destroyJobCategory(JobCategory $jobCategory)
    {
        abort_if($jobCategory->company_id !== Auth::user()->company_id, 403);
        $jobCategory->delete();
        return redirect()->route('settings.job_types.index')->with('success', 'Catégorie de poste supprimée.');
    }

    // -- Unit Types --
    public function storeUnitType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50', 'symbol' => 'nullable|string|max:10']);
        Auth::user()->company->unitTypes()->create($request->only(['name', 'symbol']));
        return redirect()->route('settings.unit_types.index')->with('success', 'Unité ajoutée.');
    }

    public function destroyUnitType(UnitType $unitType)
    {
        abort_if($unitType->company_id !== Auth::user()->company_id, 403);
        $unitType->delete();
        return redirect()->route('settings.unit_types.index')->with('success', 'Unité supprimée.');
    }

    // -- Expense Categories --
    public function storeExpenseCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        Auth::user()->company->expenseCategories()->create(['name' => $request->name]);
        return redirect()->route('settings.expense_categories.index')->with('success', 'Catégorie ajoutée.');
    }

    public function destroyExpenseCategory(ExpenseCategory $expenseCategory)
    {
        abort_if($expenseCategory->company_id !== Auth::user()->company_id, 403);
        $expenseCategory->delete();
        return redirect()->route('settings.expense_categories.index')->with('success', 'Catégorie supprimée.');
    }

    // -- Material Categories --
    public function storeMaterialCategory(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);
        Auth::user()->company->materialCategories()->create([
            'name'  => $request->name,
            'color' => $request->color ?? '#6c757d',
        ]);
        return redirect()->route('settings.material_categories.index')->with('success', 'Catégorie matériaux ajoutée.');
    }

    public function destroyMaterialCategory(MaterialCategory $materialCategory)
    {
        abort_if($materialCategory->company_id !== Auth::user()->company_id, 403);
        $materialCategory->delete();
        return redirect()->route('settings.material_categories.index')->with('success', 'Catégorie supprimée.');
    }

    // -- Salary Rates --
    public function storeSalaryRate(Request $request)
    {
        $request->validate([
            'job_type_id'    => 'required|exists:job_types,id',
            'region_id'      => 'nullable|exists:regions,id',
            'daily_rate'     => 'required|numeric|min:0',
            'hourly_rate'    => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'notes'          => 'nullable|string|max:255',
        ]);
        Auth::user()->company->salaryRates()->create($request->only([
            'job_type_id', 'region_id', 'daily_rate', 'hourly_rate', 'effective_date', 'notes',
        ]));
        return redirect()->route('settings.salary_rates.index')->with('success', 'Grille salariale ajoutée.');
    }

    public function destroySalaryRate(SalaryRate $salaryRate)
    {
        abort_if($salaryRate->company_id !== Auth::user()->company_id, 403);
        $salaryRate->delete();
        return redirect()->route('settings.salary_rates.index')->with('success', 'Entrée salariale supprimée.');
    }
}
