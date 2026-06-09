<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $company = currentCompany();
        $query   = $company->stockMovements()
                           ->with(['warehouse', 'material', 'project', 'author'])
                           ->latest('movement_date');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('item_name', 'like', '%' . $request->search . '%');
        }

        $movements  = $query->paginate(30)->withQueryString();
        $warehouses = $company->warehouses()->active()->get();

        return view('stock-movements.index', compact('movements', 'warehouses'));
    }

    public function dashboard()
    {
        $company    = currentCompany();
        $warehouses = $company->warehouses()->active()->get();
        $recent     = $company->stockMovements()
                              ->with(['warehouse', 'material'])
                              ->latest('movement_date')
                              ->take(10)
                              ->get();

        // Stock actuel par entrepôt et article
        $stockByItem = $company->stockMovements()
            ->selectRaw('warehouse_id, item_name, unit, SUM(CASE WHEN type="entree" THEN quantity WHEN type="sortie" THEN -quantity ELSE 0 END) as balance')
            ->groupBy('warehouse_id', 'item_name', 'unit')
            ->having('balance', '!=', 0)
            ->with('warehouse.project')
            ->get()
            ->groupBy('warehouse_id');

        return view('stock-movements.dashboard', compact('warehouses', 'recent', 'stockByItem'));
    }

    public function create()
    {
        $company    = currentCompany();
        $warehouses = $company->warehouses()->active()->get();
        $materials  = $company->materials()->orderBy('name')->get();
        $projects   = $company->projects()->active()->orderBy('name')->get();
        return view('stock-movements.form', compact('warehouses', 'materials', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id'             => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|required_if:type,transfert|exists:warehouses,id',
            'material_id'              => 'nullable|exists:materials,id',
            'project_id'               => 'nullable|exists:projects,id',
            'item_name'                => 'required|string|max:200',
            'unit'                     => 'required|string|max:30',
            'type'                     => 'required|in:entree,sortie,transfert,ajustement',
            'quantity'                 => 'required|numeric|min:0.001',
            'unit_cost'                => 'nullable|numeric|min:0',
            'reference'                => 'nullable|string|max:80',
            'notes'                    => 'nullable|string',
            'movement_date'            => 'required|date',
        ]);

        $company = currentCompany();

        if ($validated['type'] === 'transfert') {
            \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $company) {
                // 1. Sortie du dépôt source
                $company->stockMovements()->create(array_merge($validated, [
                    'type'       => 'sortie',
                    'notes'      => ($validated['notes'] ?? '') . ' (Transfert vers ' . Warehouse::find($validated['destination_warehouse_id'])->name . ')',
                    'created_by' => Auth::id(),
                    'unit_cost'  => $validated['unit_cost'] ?? 0,
                ]));

                // 2. Entrée dans le dépôt destination
                $company->stockMovements()->create(array_merge($validated, [
                    'warehouse_id' => $validated['destination_warehouse_id'],
                    'type'         => 'entree',
                    'notes'        => ($validated['notes'] ?? '') . ' (Transfert depuis ' . Warehouse::find($validated['warehouse_id'])->name . ')',
                    'created_by'   => Auth::id(),
                    'unit_cost'    => $validated['unit_cost'] ?? 0,
                ]));
            });

            return redirect()->route('stock-movements.index')->with('success', 'Transfert de stock effectué.');
        }

        $movement = $company->stockMovements()->create(array_merge($validated, [
            'created_by' => Auth::id(),
            'unit_cost'  => $validated['unit_cost'] ?? 0,
        ]));

        if ($movement->project_id) {
            \App\Models\ProjectLog::log(
                $movement->project_id,
                'stock_movement',
                "Mouvement de stock ({$movement->typeLabel()}) : {$movement->quantity} {$movement->unit} de '{$movement->item_name}'."
            );
        }

        return redirect()->route('stock-movements.index')->with('success', 'Mouvement de stock enregistré.');
    }

    public function show(StockMovement $stockMovement)
    {
        abort_if($stockMovement->company_id !== currentCompany()->id, 403);
        $stockMovement->load(['warehouse', 'material', 'project', 'author']);
        return view('stock-movements.show', compact('stockMovement'));
    }

    public function destroy(StockMovement $stockMovement)
    {
        abort_if($stockMovement->company_id !== currentCompany()->id, 403);
        $stockMovement->delete();
        return redirect()->route('stock-movements.index')->with('success', 'Mouvement supprimé.');
    }

    public function export(Request $request)
    {
        // Basic CSV export
        $company   = currentCompany();
        $movements = $company->stockMovements()
                             ->with(['warehouse', 'material'])
                             ->latest('movement_date')
                             ->get();

        $rows   = [['Date', 'Type', 'Article', 'Unité', 'Quantité', 'PU', 'Total', 'Dépôt', 'Référence']];
        foreach ($movements as $m) {
            $rows[] = [
                $m->movement_date->format('d/m/Y'),
                $m->typeLabel(),
                $m->item_name,
                $m->unit,
                $m->quantity,
                $m->unit_cost,
                $m->total,
                $m->warehouse->name ?? '—',
                $m->reference ?? '',
            ];
        }

        $filename = 'stock-' . now()->format('Y-m-d') . '.csv';
        $handle   = fopen('php://output', 'w');
        ob_start();
        foreach ($rows as $row) {
            fputcsv($handle, $row, ';');
        }
        fclose($handle);
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
