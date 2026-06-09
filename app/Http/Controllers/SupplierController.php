<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $company = auth()->user()->company;
        $query = $company->suppliers()->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $suppliers = $query->paginate(20)->withQueryString();

        $stats = [
            'total_count' => $company->suppliers()->count(),
            'by_type'     => $company->suppliers()->selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type'),
        ];

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    public function create()
    {
        return view('suppliers.form');
    }

    public function store(Request $request)
    {
        $validated = $this->validateSupplier($request);
        Supplier::create($validated);
        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur créé.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'employees' => fn($q) => $q->with('jobType')->latest()->take(10),
            'expenses' => fn($q) => $q->latest()->take(10),
            'purchaseOrders' => fn($q) => $q->with('project')->latest()->take(10),
        ]);

        $stats = [
            'total_spent'     => $supplier->expenses()->sum('total_amount'),
            'orders_count'    => $supplier->purchaseOrders()->count(),
            'pending_orders'  => $supplier->purchaseOrders()->whereIn('status', ['envoye', 'partiellement_livre'])->count(),
            'employees_count' => $supplier->employees()->count(),
        ];

        return view('suppliers.show', compact('supplier', 'stats'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $this->validateSupplier($request);
        $supplier->update($validated);
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur supprimé.');
    }

    private function validateSupplier(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:191'],
            'contact_name' => ['nullable', 'string', 'max:191'],
            'email'        => ['nullable', 'email', 'max:191'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'address'      => ['nullable', 'string', 'max:500'],
            'city'         => ['nullable', 'string', 'max:100'],
            'nif'          => ['nullable', 'string', 'max:30'],
            'type'         => ['required', 'in:fournisseur,sous_traitant,les_deux'],
            'notes'        => ['nullable', 'string'],
            'is_active'    => ['boolean'],
        ]);
    }
}
