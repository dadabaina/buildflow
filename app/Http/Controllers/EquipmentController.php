<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $company = currentCompany();
        $query   = $company->equipments()->with('company')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $equipments = $query->paginate(25)->withQueryString();
        $categories = $company->equipments()->distinct()->pluck('category')->filter();
        $suppliers  = \App\Models\Supplier::orderBy('name')->get();

        return view('equipments.index', compact('equipments', 'categories', 'suppliers'));
    }

    public function create()
    {
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        return view('equipments.form', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:200',
            'reference'         => 'nullable|string|max:50',
            'category'          => 'nullable|string|max:100',
            'brand'             => 'nullable|string|max:100',
            'model'             => 'nullable|string|max:100',
            'serial_number'     => 'nullable|string|max:100',
            'acquisition_date'  => 'nullable|date',
            'acquisition_cost'  => 'nullable|numeric|min:0',
            'is_internal'       => 'required|boolean',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'daily_rental_cost' => 'nullable|numeric|min:0',
            'status'            => 'required|in:disponible,affecte,maintenance,hors_service',
            'notes'             => 'nullable|string',
        ]);

        $equipment = currentCompany()->equipments()->create($validated);
        return redirect()->route('equipments.show', $equipment)->with('success', 'Matériel créé.');
    }

    public function show(Equipment $equipment)
    {
        abort_if($equipment->company_id !== currentCompany()->id, 403);
        $equipment->load(['maintenances', 'projectAssignments.project']);
        return view('equipments.show', compact('equipment'));
    }

    public function edit(Equipment $equipment)
    {
        abort_if($equipment->company_id !== currentCompany()->id, 403);
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        return view('equipments.form', compact('equipment', 'suppliers'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        abort_if($equipment->company_id !== currentCompany()->id, 403);
        $validated = $request->validate([
            'name'              => 'required|string|max:200',
            'reference'         => 'nullable|string|max:50',
            'category'          => 'nullable|string|max:100',
            'brand'             => 'nullable|string|max:100',
            'model'             => 'nullable|string|max:100',
            'serial_number'     => 'nullable|string|max:100',
            'acquisition_date'  => 'nullable|date',
            'acquisition_cost'  => 'nullable|numeric|min:0',
            'is_internal'       => 'required|boolean',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'daily_rental_cost' => 'nullable|numeric|min:0',
            'status'            => 'required|in:disponible,affecte,maintenance,hors_service',
            'notes'             => 'nullable|string',
        ]);
        $equipment->update($validated);
        return redirect()->route('equipments.show', $equipment)->with('success', 'Matériel mis à jour.');
    }

    public function destroy(Equipment $equipment)
    {
        abort_if($equipment->company_id !== currentCompany()->id, 403);
        $equipment->delete();
        return redirect()->route('equipments.index')->with('success', 'Matériel supprimé.');
    }

    public function storeMaintenance(Request $request, Equipment $equipment)
    {
        abort_if($equipment->company_id !== currentCompany()->id, 403);
        $validated = $request->validate([
            'type'                  => 'required|in:preventive,corrective',
            'maintenance_date'      => 'required|date',
            'description'           => 'nullable|string',
            'cost'                  => 'nullable|numeric|min:0',
            'performed_by'          => 'nullable|string|max:150',
            'next_maintenance_date' => 'nullable|date',
        ]);
        $equipment->maintenances()->create(array_merge($validated, [
            'company_id' => $equipment->company_id,
        ]));
        return back()->with('success', 'Maintenance enregistrée.');
    }

    public function destroyMaintenance(Equipment $equipment, EquipmentMaintenance $maintenance)
    {
        abort_if($equipment->company_id !== currentCompany()->id, 403);
        $maintenance->delete();
        return back()->with('success', 'Maintenance supprimée.');
    }
}
