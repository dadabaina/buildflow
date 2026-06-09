<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Models\Region;
use App\Models\UnitType;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::where('company_id', currentCompany()->id)
            ->with('category')
            ->withCount('prices');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->category_id) {
            $query->where('material_category_id', $request->category_id);
        }
        if ($request->unit) {
            $query->where('unit', $request->unit);
        }

        $materials   = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories  = MaterialCategory::where('company_id', currentCompany()->id)
            ->where('is_active', true)->orderBy('name')->get();
        $unitTypes   = UnitType::where('company_id', currentCompany()->id)
            ->where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total_count'    => Material::where('company_id', currentCompany()->id)->count(),
            'active_count'   => Material::where('company_id', currentCompany()->id)->where('is_active', true)->count(),
            'category_count' => $categories->count(),
        ];

        return view('materials.index', compact('materials', 'categories', 'unitTypes', 'stats'));
    }

    public function create()
    {
        $companyId = currentCompany()->id;
        $categories = MaterialCategory::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get();
        $unitTypes = UnitType::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get();

        return view('materials.form', compact('categories', 'unitTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:150',
            'description'          => 'nullable|string',
            'unit'                 => 'required|string|max:30',
            'reference'            => 'nullable|string|max:50',
            'material_category_id' => 'nullable|exists:material_categories,id',
            'min_stock_level'      => 'nullable|numeric|min:0',
            // Prix initial optionnel
            'unit_price'           => 'nullable|numeric|min:0',
            'region_id'            => 'nullable|exists:regions,id',
        ]);

        $material = Material::create([
            'company_id'           => currentCompany()->id,
            'name'                 => $data['name'],
            'description'          => $data['description'],
            'unit'                 => $data['unit'],
            'reference'            => $data['reference'],
            'material_category_id' => $data['material_category_id'],
            'min_stock_level'      => $data['min_stock_level'] ?? 0,
        ]);

        // Créer le prix initial si fourni
        if (!empty($data['unit_price'])) {
            MaterialPrice::create([
                'material_id'    => $material->id,
                'company_id'     => currentCompany()->id,
                'region_id'      => $data['region_id'] ?? null,
                'unit_price'     => $data['unit_price'],
                'effective_date' => now()->toDateString(),
            ]);
        }

        return redirect()->route('materials.show', $material)
            ->with('success', 'Matériau créé avec succès.');
    }

    public function show(Material $material)
    {
        $this->authorizeCompany($material);
        $material->load(['category', 'prices.region', 'dosageItems.dosageModel']);
        $regions = Region::where('company_id', currentCompany()->id)->orderBy('name')->get();

        return view('materials.show', compact('material', 'regions'));
    }

    public function edit(Material $material)
    {
        $this->authorizeCompany($material);
        $companyId = currentCompany()->id;
        $categories = MaterialCategory::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get();
        $unitTypes = UnitType::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get();

        return view('materials.form', compact('material', 'categories', 'unitTypes'));
    }

    public function update(Request $request, Material $material)
    {
        $this->authorizeCompany($material);

        $data = $request->validate([
            'name'                 => 'required|string|max:150',
            'description'          => 'nullable|string',
            'unit'                 => 'required|string|max:30',
            'reference'            => 'nullable|string|max:50',
            'material_category_id' => 'nullable|exists:material_categories,id',
            'min_stock_level'      => 'nullable|numeric|min:0',
            'is_active'            => 'boolean',
        ]);

        $material->update($data);

        return redirect()->route('materials.show', $material)
            ->with('success', 'Matériau mis à jour.');
    }

    public function destroy(Material $material)
    {
        $this->authorizeCompany($material);
        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Matériau supprimé.');
    }

    // ── Gestion des prix ──────────────────────────────────────────────────────

    public function storePrice(Request $request, Material $material)
    {
        $this->authorizeCompany($material);

        $data = $request->validate([
            'unit_price'     => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'region_id'      => 'nullable|exists:regions,id',
            'supplier_name'  => 'nullable|string|max:150',
            'notes'          => 'nullable|string',
        ]);

        MaterialPrice::create([
            ...$data,
            'material_id' => $material->id,
            'company_id'  => currentCompany()->id,
        ]);

        return back()->with('success', 'Prix ajouté.');
    }

    public function destroyPrice(Material $material, MaterialPrice $price)
    {
        $this->authorizeCompany($material);
        abort_unless($price->material_id === $material->id, 404);
        $price->delete();

        return back()->with('success', 'Prix supprimé.');
    }

    // ── Export CSV ────────────────────────────────────────────────────────────

    public function exportCsv()
    {
        $materials = Material::with(['category', 'prices' => fn($q) => $q->orderByDesc('effective_date')->limit(1)])
            ->where('company_id', currentCompany()->id)
            ->orderBy('name')
            ->get();

        $filename = 'materiaux_' . now()->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($materials) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Référence', 'Désignation', 'Catégorie', 'Unité', 'Prix actuel HT', 'Date prix', 'Actif'], ';');
            foreach ($materials as $mat) {
                fputcsv($handle, [
                    $mat->reference,
                    $mat->name,
                    $mat->category->name ?? '',
                    $mat->unit,
                    $mat->prices->first()?->unit_price ?? '',
                    $mat->prices->first() ? $mat->prices->first()->effective_date->format('d/m/Y') : '',
                    $mat->is_active ? 'Oui' : 'Non',
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── AJAX — liste matériaux pour autocomplete ──────────────────────────────

    public function search(Request $request)
    {
        $term = $request->get('q', '');

        $materials = Material::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->where('name', 'like', "%{$term}%")
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'unit', 'reference']);

        return response()->json($materials);
    }

    public function stockBreakdown(Material $material)
    {
        $this->authorizeCompany($material);

        $breakdown = \App\Models\StockMovement::where('material_id', $material->id)
            ->selectRaw('warehouse_id, SUM(CASE WHEN type="entree" THEN quantity WHEN type="sortie" THEN -quantity ELSE 0 END) as balance')
            ->groupBy('warehouse_id')
            ->with('warehouse.project')
            ->having('balance', '!=', 0)
            ->get();

        return view('materials.stock_breakdown_modal', compact('material', 'breakdown'));
    }

    private function authorizeCompany(Material $material): void
    {
        abort_unless($material->company_id === currentCompany()->id, 403);
    }
}
