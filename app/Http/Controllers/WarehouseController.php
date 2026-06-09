<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = currentCompany()->warehouses()->withCount('stockMovements')->get();
        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $projects = currentCompany()->projects()->active()->orderBy('name')->get();
        return view('warehouses.form', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:150',
            'project_id' => 'nullable|exists:projects,id',
            'location'   => 'nullable|string|max:200',
            'is_active'  => 'boolean',
        ]);
        currentCompany()->warehouses()->create($validated);
        return redirect()->route('warehouses.index')->with('success', 'Dépôt créé.');
    }

    public function edit(Warehouse $warehouse)
    {
        abort_if($warehouse->company_id !== currentCompany()->id, 403);
        $projects = currentCompany()->projects()->active()->orderBy('name')->get();
        return view('warehouses.form', compact('warehouse', 'projects'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        abort_if($warehouse->company_id !== currentCompany()->id, 403);
        $validated = $request->validate([
            'name'       => 'required|string|max:150',
            'project_id' => 'nullable|exists:projects,id',
            'location'   => 'nullable|string|max:200',
            'is_active'  => 'boolean',
        ]);
        $warehouse->update($validated);
        return redirect()->route('warehouses.index')->with('success', 'Dépôt mis à jour.');
    }

    public function destroy(Warehouse $warehouse)
    {
        abort_if($warehouse->company_id !== currentCompany()->id, 403);
        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'Dépôt supprimé.');
    }
}
