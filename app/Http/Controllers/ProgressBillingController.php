<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ProgressBilling;
use App\Models\ProgressBillingLine;
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
        $quotesData = $this->buildQuotesData($quotes);
        return view('progress-billings.form', compact('projects', 'quotes', 'selected', 'quotesData'));
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
        $quotesData = $this->buildQuotesData($quotes, $progressBilling->id);
        return view('progress-billings.form', compact('progressBilling', 'projects', 'quotes', 'quotesData'));
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

        // Garde anti-dépassement du marché : situations + conversions directes
        // ne doivent pas facturer plus que le montant contractuel du chantier.
        $contractAmount  = (float) $progressBilling->project->contract_amount;
        $alreadyInvoiced = (float) Invoice::where('project_id', $progressBilling->project_id)
            ->where('status', '!=', 'annulee')
            ->sum('total_ttc');

        if ($contractAmount > 0 && $alreadyInvoiced + (float) $progressBilling->total_ttc > $contractAmount + 0.01) {
            return back()->with('error', sprintf(
                'Facturation impossible : %s Ar déjà facturés sur ce chantier, facturer cette situation (%s Ar) dépasserait le montant du marché (%s Ar).',
                number_format($alreadyInvoiced, 0, ',', ' '),
                number_format((float) $progressBilling->total_ttc, 0, ',', ' '),
                number_format($contractAmount, 0, ',', ' ')
            ));
        }

        return DB::transaction(function () use ($progressBilling) {
            $company = Auth::user()->company;

            $invoice = $company->invoices()->create([
                'project_id'       => $progressBilling->project_id,
                'client_id'        => $progressBilling->project->client_id,
                'quote_id'         => $progressBilling->quote_id,
                'created_by'       => Auth::id(),
                'reference'        => $company->nextInvoiceReference(),
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

    /**
     * Construit, pour chaque devis proposé, la liste de ses lignes avec le
     * pourcentage déjà facturé lors de situations antérieures (report
     * automatique du "% précédent"). Sert à pré-remplir le formulaire côté
     * client sans aller-retour AJAX.
     */
    private function buildQuotesData($quotes, ?int $excludeBillingId = null): array
    {
        $itemIds = $quotes->flatMap(fn($q) => $q->items->pluck('id'))->all();

        $previousPct = collect();
        if (!empty($itemIds)) {
            $previousPct = ProgressBillingLine::whereIn('quote_item_id', $itemIds)
                ->whereHas('progressBilling', function ($q) use ($excludeBillingId) {
                    $q->where('status', '!=', 'annule');
                    if ($excludeBillingId) {
                        $q->where('id', '!=', $excludeBillingId);
                    }
                })
                ->join('progress_billings', 'progress_billing_lines.progress_billing_id', '=', 'progress_billings.id')
                ->orderByDesc('progress_billings.situation_number')
                ->get(['progress_billing_lines.quote_item_id', 'progress_billing_lines.cumulative_pct'])
                ->unique('quote_item_id')
                ->pluck('cumulative_pct', 'quote_item_id');
        }

        $data = [];
        foreach ($quotes as $quote) {
            $data[$quote->id] = [
                'items' => $quote->items->map(fn($item) => [
                    'quote_item_id' => $item->id,
                    'description'   => $item->description,
                    'quantity'      => (float) $item->quantity,
                    'unit'          => $item->unit,
                    'unit_price'    => (float) $item->unit_price,
                    'previous_pct'  => (float) ($previousPct[$item->id] ?? 0),
                ])->values(),
            ];
        }

        return $data;
    }
}
