<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\JobCategory;
use App\Models\JobType;
use App\Models\MaterialCategory;
use App\Models\Region;
use App\Models\SalaryRate;
use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
