<?php

namespace App\Http\Controllers;

use App\Models\ProgressBilling;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressBillingController extends Controller
{
    public function index(Request $request)
    {
        $query = ProgressBilling::with(['project', 'quote', 'createdBy'])->latest();

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $billings = $query->paginate(25)->withQueryString();
        $projects = Project::orderBy('name')->get();

        return view('progress-billings.index', compact('billings', 'projects'));
    }

    public function create(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $quotes   = collect();
        $selected = $request->input('project_id');
        if ($selected) {
            $quotes = Quote::where('project_id', $selected)->whereIn('status', ['accepte'])->with('items')->get();
        }
        return view('progress-billings.form', compact('projects', 'quotes', 'selected'));
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $data['created_by'] = Auth::id();

        // Auto-reference SIT-YYYY-NNN-Sxx
        $company    = Auth::user()->company;
        $lastNum    = $company->progressBillings()->max(DB::raw("CAST(SUBSTRING_INDEX(SUBSTRING(reference, 5), '-S', 1) AS UNSIGNED)")) ?? 0;
        $sitNum     = ($company->progressBillings()->where('project_id', $data['project_id'])->count()) + 1;
        $data['reference']       = 'SIT-' . now()->year . '-' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT) . '-S' . str_pad($sitNum, 2, '0', STR_PAD_LEFT);
        $data['situation_number'] = $sitNum;

        DB::transaction(function () use ($data, $request) {
            $billing = ProgressBilling::create($data);
            $this->syncLines($billing, $request->input('lines', []));
        });

        return redirect()->route('progress-billings.index')
            ->with('success', 'Situation de travaux créée.');
    }

    public function show(ProgressBilling $progressBilling)
    {
        $progressBilling->load(['project', 'quote', 'invoice', 'createdBy', 'lines.quoteItem']);
        return view('progress-billings.show', compact('progressBilling'));
    }

    public function edit(ProgressBilling $progressBilling)
    {
        if (!in_array($progressBilling->status, ['brouillon'])) {
            return back()->with('error', 'Seule une situation en brouillon peut être modifiée.');
        }
        $progressBilling->load('lines');
        $projects = Project::orderBy('name')->get();
        $quotes   = Quote::where('project_id', $progressBilling->project_id)->whereIn('status', ['accepte'])->with('items')->get();
        return view('progress-billings.form', compact('progressBilling', 'projects', 'quotes'));
    }

    public function update(Request $request, ProgressBilling $progressBilling)
    {
        if (!in_array($progressBilling->status, ['brouillon'])) {
            return back()->with('error', 'Seule une situation en brouillon peut être modifiée.');
        }
        $data = $this->validateRequest($request);

        DB::transaction(function () use ($data, $request, $progressBilling) {
            $progressBilling->update($data);
            $progressBilling->lines()->delete();
            $this->syncLines($progressBilling, $request->input('lines', []));
        });

        return redirect()->route('progress-billings.show', $progressBilling)
            ->with('success', 'Situation mise à jour.');
    }

    public function destroy(ProgressBilling $progressBilling)
    {
        $progressBilling->delete();
        return redirect()->route('progress-billings.index')
            ->with('success', 'Situation supprimée.');
    }

    public function send(ProgressBilling $progressBilling)
    {
        if ($progressBilling->status !== 'brouillon') {
            return back()->with('error', 'Seule une situation en brouillon peut être envoyée.');
        }
        $progressBilling->update(['status' => 'envoye']);
        return back()->with('success', 'Situation marquée comme envoyée.');
    }

    public function validateBilling(ProgressBilling $progressBilling)
    {
        if ($progressBilling->status !== 'envoye') {
            return back()->with('error', 'Seule une situation envoyée peut être validée.');
        }
        $progressBilling->update(['status' => 'valide']);
        return back()->with('success', 'Situation validée.');
    }

    public function generateInvoice(ProgressBilling $progressBilling)
    {
        if ($progressBilling->status !== 'valide') {
            return back()->with('error', 'Seule une situation validée peut être facturée.');
        }

        return DB::transaction(function () use ($progressBilling) {
            $company = Auth::user()->company;
            $prefix  = $company->invoice_prefix ?? 'FAC';
            $lastNum = $company->invoices()->max(DB::raw("CAST(SUBSTRING(reference, LENGTH('{$prefix}')+2) AS UNSIGNED)")) ?? 0;
            $reference = $prefix . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

            $invoice = $company->invoices()->create([
                'project_id'       => $progressBilling->project_id,
                'client_id'        => $progressBilling->project->client_id,
                'quote_id'         => $progressBilling->quote_id,
                'created_by'       => Auth::id(),
                'reference'        => $reference,
                'title'            => "Facture pour " . $progressBilling->title,
                'type'             => 'situation',
                'invoice_date'     => now(),
                'due_date'         => $progressBilling->due_date ?? now()->addDays(30),
                'tva_rate'         => $progressBilling->tva_rate,
                'rg_rate'          => $progressBilling->rg_rate,
                'subtotal_ht'      => $progressBilling->subtotal_ht,
                'tva_amount'       => $progressBilling->tva_amount,
                'total_ttc'        => $progressBilling->total_ttc,
                'rg_amount'        => $progressBilling->rg_amount,
                'net_to_pay'       => $progressBilling->net_to_pay,
                'amount_paid'      => 0,
                'amount_remaining' => $progressBilling->net_to_pay,
                'status'           => 'brouillon',
            ]);

            foreach ($progressBilling->lines as $line) {
                $invoice->items()->create([
                    'description' => $line->description,
                    'quantity'    => $line->quote_quantity * ($line->current_pct / 100),
                    'unit'        => $line->unit,
                    'unit_price'  => $line->unit_price,
                    'total_ht'    => $line->current_amount,
                    'sort_order'  => $line->sort_order,
                ]);
            }

            $progressBilling->update([
                'status'     => 'facture',
                'invoice_id' => $invoice->id
            ]);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Facture générée depuis la situation.');
        });
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'project_id'   => ['required', 'exists:projects,id'],
            'quote_id'     => ['nullable', 'exists:quotes,id'],
            'title'        => ['required', 'string', 'max:200'],
            'billing_date' => ['required', 'date'],
            'due_date'     => ['nullable', 'date', 'after_or_equal:billing_date'],
            'rg_rate'      => ['required', 'numeric', 'min:0', 'max:100'],
            'tva_rate'     => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'        => ['nullable', 'string'],
        ]);
    }

    private function syncLines(ProgressBilling $billing, array $lines): void
    {
        foreach ($lines as $i => $line) {
            if (empty($line['description'])) continue;
            $billing->lines()->create([
                'quote_item_id'  => $line['quote_item_id'] ?? null,
                'description'    => $line['description'],
                'quote_quantity' => (float) ($line['quote_quantity'] ?? 0),
                'unit_price'     => (float) ($line['unit_price'] ?? 0),
                'unit'           => $line['unit'] ?? null,
                'previous_pct'   => (float) ($line['previous_pct'] ?? 0),
                'current_pct'    => (float) ($line['current_pct'] ?? 0),
                'sort_order'     => $i,
            ]);
        }
        $billing->recalcTotals();
    }
}
