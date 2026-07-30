<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('invoices.view');
        $company = Auth::user()->company;
        $query = $company->invoices()->with(['project', 'client']);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($type = $request->type) {
            $query->where('type', $type);
        }
        if ($projectId = $request->project_id) {
            $query->where('project_id', $projectId);
        }

        $invoices = $query->orderByDesc('invoice_date')->paginate(20)->withQueryString();
        $projects = $company->projects()->orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'projects'));
    }

    public function create(Request $request)
    {
        $this->authorize('invoices.create');
        $company  = Auth::user()->company;
        $projects = $company->projects()->orderBy('name')->get();
        $clients  = $company->clients()->orderBy('name')->get();

        return view('invoices.form', compact('projects', 'clients'));
    }

    public function store(Request $request)
    {
        $this->authorize('invoices.create');
        $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'client_id'    => 'required|exists:clients,id',
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:standard,acompte,situation,avoir',
            'invoice_date' => 'required|date',
            'due_date'     => 'nullable|date',
            'tva_rate'     => 'nullable|numeric|min:0|max:100',
            'rg_rate'      => 'nullable|numeric|min:0|max:100',
        ]);

        $company = Auth::user()->company;
        $project = $company->projects()->findOrFail($request->project_id);
        $clientId = $request->client_id ?? $project->client_id;

        $invoice = DB::transaction(fn () => $company->invoices()->create([
            'project_id'       => $project->id,
            'client_id'        => $clientId,
            'created_by'       => Auth::id(),
            'reference'        => $company->nextInvoiceReference(),
            'title'            => $request->title,
            'type'             => $request->type,
            'invoice_date'     => $request->invoice_date,
            'due_date'         => $request->due_date,
            'tva_rate'         => $request->tva_rate ?? 20,
            'rg_rate'          => $request->rg_rate ?? 0,
            'subtotal_ht'      => 0,
            'tva_amount'       => 0,
            'total_ttc'        => 0,
            'rg_amount'        => 0,
            'net_to_pay'       => 0,
            'amount_paid'      => 0,
            'amount_remaining' => 0,
            'status'           => 'brouillon',
            'notes'            => $request->notes,
        ]));

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Facture créée. Ajoutez maintenant les lignes.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);
        $invoice->load(['project', 'client', 'items', 'payments', 'quote', 'createdBy']);

        $company = Auth::user()->company;
        $unitTypes = $company->unitTypes()->where('is_active', true)->orderBy('name')->get();

        return view('invoices.show', compact('invoice', 'unitTypes'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice, 'invoices.edit');
        if ($invoice->status === 'soldee') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Une facture soldée ne peut plus être modifiée.');
        }

        $company  = Auth::user()->company;
        $projects = $company->projects()->orderBy('name')->get();
        $clients  = $company->clients()->orderBy('name')->get();

        return view('invoices.form', compact('invoice', 'projects', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($invoice, 'invoices.edit');
        if ($invoice->status === 'soldee') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Une facture soldée ne peut plus être modifiée.');
        }

        $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'client_id'    => 'required|exists:clients,id',
            'title'        => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'tva_rate'     => 'nullable|numeric|min:0|max:100',
            'rg_rate'      => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice->update($request->only([
            'project_id', 'client_id', 'title', 'type', 'invoice_date', 'due_date',
            'tva_rate', 'rg_rate', 'notes',
        ]));

        $this->recalculate($invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Facture mise à jour.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice, 'invoices.delete');
        if ($invoice->status === 'soldee') {
            return back()->with('error', 'Une facture soldée ne peut pas être supprimée.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Facture supprimée.');
    }

    public function markSent(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice, 'invoices.send');
        if ($invoice->status === 'soldee') {
            return back()->with('error', 'Une facture soldée ne peut pas être modifiée.');
        }
        $invoice->update(['status' => 'envoye']);

        return back()->with('success', 'Facture marquée comme envoyée.');
    }

    public function cancel(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice, 'invoices.edit');
        if ($invoice->status === 'soldee') {
            return back()->with('error', 'Une facture soldée ne peut pas être annulée.');
        }
        $invoice->update(['status' => 'annulee']);

        return back()->with('success', 'Facture annulée.');
    }

    public function addItem(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($invoice, 'invoices.edit');
        $request->validate([
            'description'  => 'required|string',
            'quantity'     => 'required|numeric|min:0',
            'unit_price'   => 'required|numeric|min:0',
            'unit_type_id' => 'nullable|exists:unit_types,id',
        ]);

        $lastOrder = $invoice->items()->max('sort_order') ?? 0;
        $totalHt = round($request->quantity * $request->unit_price, 2);

        $invoice->items()->create([
            'description'  => $request->description,
            'quantity'     => $request->quantity,
            'unit_type_id' => $request->unit_type_id,
            'unit_price'   => $request->unit_price,
            'total_ht'     => $totalHt,
            'sort_order'   => $lastOrder + 1,
        ]);

        $this->recalculate($invoice);

        return back()->with('success', 'Ligne ajoutée.');
    }

    public function removeItem(Invoice $invoice, InvoiceItem $item)
    {
        $this->authorizeInvoice($invoice, 'invoices.edit');
        abort_if($item->invoice_id !== $invoice->id, 403);
        $item->delete();
        $this->recalculate($invoice);

        return back()->with('success', 'Ligne supprimée.');
    }

    private function recalculate(Invoice $invoice): void
    {
        $invoice->refresh();
        $subtotal = $invoice->items()->sum('total_ht');
        $tva      = round($subtotal * $invoice->tva_rate / 100, 2);
        $ttc      = $subtotal + $tva;
        $rg       = round($ttc * $invoice->rg_rate / 100, 2);
        $netToPay = $ttc - $rg;
        $paid     = $invoice->payments()->sum('payment_allocations.amount');
        $remaining = max(0, $netToPay - $paid);

        $status = 'brouillon';
        if ($invoice->status !== 'brouillon') {
            $status = $remaining <= 0 ? 'soldee' : ($paid > 0 ? 'partiellement_payee' : $invoice->status);
        }

        $invoice->update([
            'subtotal_ht'      => $subtotal,
            'tva_amount'       => $tva,
            'total_ttc'        => $ttc,
            'rg_amount'        => $rg,
            'net_to_pay'       => $netToPay,
            'amount_paid'      => $paid,
            'amount_remaining' => $remaining,
            'status'           => $status,
        ]);
    }

    private function authorizeInvoice(Invoice $invoice, string $permission = 'invoices.view'): void
    {
        abort_if($invoice->company_id !== Auth::user()->company_id, 403);
        $this->authorize($permission);
    }
}
