<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Project;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['project', 'supplier', 'createdBy'])->latest();

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                  ->orWhereHas('supplier', fn($q2) => $q2->where('name', 'like', "%{$s}%"));
            });
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        $purchaseOrders = $query->paginate(25)->withQueryString();
        $projects       = Project::orderBy('name')->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'projects'));
    }

    public function create(Request $request)
    {
        $projects  = Project::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $selected  = $request->input('project_id');
        return view('purchase-orders.form', compact('projects', 'suppliers', 'selected'));
    }

    public function store(Request $request)
    {
        $data = $this->validateOrder($request);
        $data['created_by'] = Auth::id();

        $po = DB::transaction(function () use ($data, $request) {
            $po = PurchaseOrder::create($data);
            $this->syncItems($po, $request->input('items', []));
            return $po;
        });

        \App\Models\ProjectLog::log(
            $po->project_id,
            'bc_created',
            "Nouveau Bon de Commande créé : {$po->reference} auprès de {$po->supplier->name}."
        );

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Bon de commande créé.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['project', 'supplier', 'createdBy', 'items']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['brouillon', 'valide'])) {
            return back()->with('error', 'Seuls les BCs en brouillon ou validé peuvent être modifiés.');
        }
        $purchaseOrder->load('items');
        $projects  = Project::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('purchase-orders.form', compact('purchaseOrder', 'projects', 'suppliers'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['brouillon', 'valide'])) {
            return back()->with('error', 'Seuls les BCs en brouillon ou validé peuvent être modifiés.');
        }
        $data = $this->validateOrder($request);

        DB::transaction(function () use ($data, $request, $purchaseOrder) {
            $purchaseOrder->update($data);
            $purchaseOrder->items()->delete();
            $this->syncItems($purchaseOrder, $request->input('items', []));
        });

        \App\Models\ProjectLog::log(
            $purchaseOrder->project_id,
            'bc_updated',
            "Le Bon de Commande {$purchaseOrder->reference} a été mis à jour."
        );

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Bon de commande mis à jour.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $projectId = $purchaseOrder->project_id;
        $ref = $purchaseOrder->reference;
        $purchaseOrder->delete();

        \App\Models\ProjectLog::log(
            $projectId,
            'bc_deleted',
            "Le Bon de Commande {$ref} a été supprimé."
        );

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Bon de commande supprimé.');
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate(['status' => 'required|string']);
        $newStatus = $request->input('status');

        if (!$purchaseOrder->canTransitionTo($newStatus)) {
            return back()->with('error', 'Transition de statut invalide.');
        }

        $oldStatusLib = $purchaseOrder->status_libelle;
        $purchaseOrder->update(['status' => $newStatus]);
        $newStatusLib = $purchaseOrder->fresh()->status_libelle;

        \App\Models\ProjectLog::log(
            $purchaseOrder->project_id,
            'bc_status_updated',
            "Le BC {$purchaseOrder->reference} est passé de '{$oldStatusLib}' à '{$newStatusLib}'."
        );

        return back()->with('success', 'Statut mis à jour : ' . $newStatusLib);
    }

    public function convertToExpense(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['livre', 'partiellement_livre'])) {
            return back()->with('error', 'Seul un BC livré peut être converti en dépense.');
        }

        $purchaseOrder->load('items');
        $companyId = Auth::user()->company_id;

        DB::transaction(function () use ($purchaseOrder, $companyId) {
            foreach ($purchaseOrder->items as $item) {
                Expense::create([
                    'company_id'   => $companyId,
                    'project_id'   => $purchaseOrder->project_id,
                    'supplier_id'  => $purchaseOrder->supplier_id,
                    'created_by'   => Auth::id(),
                    'description'  => $item->description,
                    'expense_date' => now()->toDateString(),
                    'quantity'     => $item->quantity,
                    'unit'         => $item->unit,
                    'unit_price'   => $item->unit_price,
                    'status'       => 'saisie',
                    'notes'        => 'Converti depuis BC ' . $purchaseOrder->reference,
                ]);
            }
        });

        \App\Models\ProjectLog::log(
            $purchaseOrder->project_id,
            'bc_converted',
            "Le BC {$purchaseOrder->reference} a été converti en dépenses réelles."
        );

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'BC converti en ' . $purchaseOrder->items->count() . ' dépense(s).');
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'project_id'          => ['required', 'exists:projects,id'],
            'supplier_id'         => ['required', 'exists:suppliers,id'],
            'order_date'          => ['required', 'date'],
            'delivery_date'       => ['nullable', 'date', 'after_or_equal:order_date'],
            'tva_rate'            => ['required', 'numeric', 'min:0', 'max:100'],
            'delivery_conditions' => ['nullable', 'string'],
            'notes'               => ['nullable', 'string'],
        ]);
    }

    private function syncItems(PurchaseOrder $po, array $items): void
    {
        foreach ($items as $i => $item) {
            if (empty($item['description'])) {
                continue;
            }
            $po->items()->create([
                'description' => $item['description'],
                'quantity'    => (float) ($item['quantity'] ?? 1),
                'unit'        => $item['unit'] ?? null,
                'unit_price'  => (float) ($item['unit_price'] ?? 0),
                'sort_order'  => $i,
            ]);
        }
        $po->recalcTotals();
    }
}
