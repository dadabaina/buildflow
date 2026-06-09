<?php

namespace App\Http\Controllers;

use App\Models\DosageItem;
use App\Models\DosageModel;
use App\Models\Material;
use App\Models\UnitType;
use App\Services\QuoteCalculationService;
use Illuminate\Http\Request;

class DosageController extends Controller
{
    public function __construct(private QuoteCalculationService $calculator) {}

    public function index()
    {
        $models = DosageModel::where('company_id', currentCompany()->id)
            ->withCount('items')
            ->orderBy('name')
            ->get();

        return view('dosage.index', compact('models'));
    }

    public function create()
    {
        $materials = Material::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $unitTypes = UnitType::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dosage.form', compact('materials', 'unitTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'description'     => 'nullable|string',
            'output_unit'     => 'required|string|max:30',
            'output_quantity' => 'required|numeric|min:0.001',
        ]);

        $model = DosageModel::create([
            ...$data,
            'company_id' => currentCompany()->id,
        ]);

        return redirect()->route('dosage.show', $model)->with('success', 'Modèle de dosage créé.');
    }

    public function show(DosageModel $dosage)
    {
        $this->authorizeCompany($dosage);
        $dosage->load(['items.material', 'items.jobType']);

        $materials = Material::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $jobTypes = \App\Models\JobType::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dosage.show', compact('dosage', 'materials', 'jobTypes'));
    }

    public function edit(DosageModel $dosage)
    {
        $this->authorizeCompany($dosage);

        $materials = Material::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $unitTypes = UnitType::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dosage.form', compact('dosage', 'materials', 'unitTypes'));
    }

    public function update(Request $request, DosageModel $dosage)
    {
        $this->authorizeCompany($dosage);

        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'description'     => 'nullable|string',
            'output_unit'     => 'required|string|max:30',
            'output_quantity' => 'required|numeric|min:0.001',
            'is_active'       => 'boolean',
        ]);

        $dosage->update($data);

        return redirect()->route('dosage.show', $dosage)->with('success', 'Modèle mis à jour.');
    }

    public function destroy(DosageModel $dosage)
    {
        $this->authorizeCompany($dosage);
        $dosage->delete();

        return redirect()->route('dosage.index')->with('success', 'Modèle supprimé.');
    }

    // ── Items management ──────────────────────────────────────────────────────

    public function storeItem(Request $request, DosageModel $dosage)
    {
        $this->authorizeCompany($dosage);

        $data = $request->validate([
            'material_id'      => 'nullable|exists:materials,id',
            'job_type_id'      => 'nullable|exists:job_types,id',
            'item_type'        => 'required|in:material,labor,equipment,subcontract',
            'description'      => 'nullable|string|max:150',
            'unit'             => 'required|string|max:30',
            'quantity_per_unit'=> 'required|numeric|min:0',
            'waste_rate'       => 'nullable|numeric|min:0|max:100',
            'unit_price'       => 'nullable|numeric|min:0',
        ]);

        $lastOrder = $dosage->items()->max('sort_order') ?? 0;

        // Si matériau lié, utilise son nom comme description par défaut
        if (!empty($data['material_id'])) {
            $mat = Material::find($data['material_id']);
            $data['description'] = $data['description'] ?: ($mat?->name ?? '');
            $data['unit']        = $data['unit'] ?: ($mat?->unit ?? '');
        }

        // Pour labor sans job_type_id, ou equipment/subcontract : description obligatoire
        if (empty($data['description'])) {
            return back()->withErrors(['description' => 'La description est obligatoire.'])->withInput();
        }

        $dosage->items()->create([
            ...$data,
            'waste_rate'  => $data['waste_rate'] ?? 0,
            'sort_order'  => $lastOrder + 1,
        ]);

        return back()->with('success', 'Ligne ajoutée.');
    }

    public function destroyItem(DosageModel $dosage, DosageItem $item)
    {
        $this->authorizeCompany($dosage);
        abort_unless($item->dosage_model_id === $dosage->id, 404);
        $item->delete();

        return back()->with('success', 'Ligne supprimée.');
    }

    // ── AJAX — Calcul DBE ─────────────────────────────────────────────────────

    /**
     * POST /dosage/calculate (AJAX)
     * Retourne le DBE calculé pour un modèle + quantité donnés.
     */
    public function calculate(Request $request)
    {
        $data = $request->validate([
            'dosage_model_id' => 'required|exists:dosage_models,id',
            'quantity'        => 'required|numeric|min:0',
            'fg_rate'         => 'nullable|numeric|min:0|max:100',
            'margin_rate'     => 'nullable|numeric|min:0|max:100',
            'alea_rate'       => 'nullable|numeric|min:0|max:100',
            'region_id'       => 'nullable|exists:regions,id',
        ]);

        // Vérifier que ce modèle appartient bien à l'entreprise
        $model = DosageModel::where('company_id', currentCompany()->id)
            ->findOrFail($data['dosage_model_id']);

        $dbeResult = $this->calculator->calculateFromDosage(
            $model->id,
            (float) $data['quantity'],
            $data['region_id'] ?? null
        );

        $priceResult = $this->calculator->applyCoefficients(
            $dbeResult['dbe_total'],
            (float) $data['quantity'],
            (float) ($data['fg_rate'] ?? 0),
            (float) ($data['margin_rate'] ?? 0),
            (float) ($data['alea_rate'] ?? 0)
        );

        return response()->json([
            ...$dbeResult,
            ...$priceResult,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function authorizeCompany(DosageModel $model): void
    {
        abort_unless($model->company_id === currentCompany()->id, 403);
    }
}
