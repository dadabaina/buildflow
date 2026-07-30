<?php

namespace App\Http\Controllers;

use App\Mail\QuoteSentMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteSection;
use App\Notifications\QuoteAccepted;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('quotes.view');
        $company = Auth::user()->company;
        $query = $company->quotes()->with(['project', 'client']);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($projectId = $request->project_id) {
            $query->where('project_id', $projectId);
        }

        $quotes = $query->orderByDesc('quote_date')->paginate(20)->withQueryString();
        $projects = $company->projects()->orderBy('name')->get();

        return view('quotes.index', compact('quotes', 'projects'));
    }

    public function create(Request $request)
    {
        $this->authorize('quotes.create');
        $company = Auth::user()->company;
        $projects = $company->projects()->orderBy('name')->get();
        $clients = $company->clients()->orderBy('name')->get();

        $selectedProject = $request->project_id
            ? $company->projects()->find($request->project_id)
            : null;

        return view('quotes.form', compact('projects', 'clients', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $this->authorize('quotes.create');
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'client_id'  => 'required|exists:clients,id',
            'title'      => 'required|string|max:255',
            'quote_date' => 'required|date',
            'valid_until'=> 'nullable|date|after_or_equal:quote_date',
            'tva_rate'   => 'nullable|numeric|min:0|max:100',
            'discount_global' => 'nullable|numeric|min:0',
            'discount_type'   => 'nullable|in:percent,amount',
        ]);

        $company = Auth::user()->company;

        $quote = DB::transaction(fn () => $company->quotes()->create([
            'reference'       => $company->nextQuoteReference(),
            'project_id'      => $request->project_id,
            'client_id'       => $request->client_id,
            'created_by'      => Auth::id(),
            'title'           => $request->title,
            'quote_date'      => $request->quote_date,
            'valid_until'     => $request->valid_until,
            'tva_rate'        => $request->tva_rate ?? 20,
            'discount_global' => $request->discount_global ?? 0,
            'discount_type'   => $request->discount_type ?? 'percent',
            'status'          => 'brouillon',
            'notes'           => $request->notes,
            'terms'           => $request->terms,
            'subtotal_ht'     => 0,
            'discount_amount' => 0,
            'taxable_ht'      => 0,
            'tva_amount'      => 0,
            'total_ttc'       => 0,
        ]));

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Devis créé. Ajoutez maintenant les lignes.');
    }

    public function show(Quote $quote)
    {
        $this->authorizeQuote($quote);
        $quote->load(['project', 'client', 'sections.items', 'items', 'createdBy', 'invoice']);
        $company      = Auth::user()->company;
        $unitTypes    = $company->unitTypes()->where('is_active', true)->orderBy('name')->get();
        $dosageModels = $company->dosageModels()->where('is_active', true)->orderBy('name')->get();

        return view('quotes.show', compact('quote', 'unitTypes', 'dosageModels', 'company'));
    }

    public function edit(Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.edit');
        $company = Auth::user()->company;
        $projects = $company->projects()->orderBy('name')->get();
        $clients  = $company->clients()->orderBy('name')->get();
        $selectedProject = $quote->project;

        return view('quotes.form', compact('quote', 'projects', 'clients', 'selectedProject'));
    }

    public function update(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.edit');
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'client_id'  => 'required|exists:clients,id',
            'title'      => 'required|string|max:255',
            'quote_date' => 'required|date',
        ]);

        // US-09-04: when modifying a sent quote, increment version
        $newVersion = $quote->version;
        if ($quote->status === 'envoye') {
            $newVersion = $quote->version + 1;
        }

        $quote->update(array_merge(
            $request->only([
                'project_id', 'client_id', 'title', 'quote_date', 'valid_until',
                'tva_rate', 'discount_global', 'discount_type', 'notes', 'terms',
            ]),
            ['version' => $newVersion]
        ));

        $quote->recalculateTotals();

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis mis à jour.');
    }

    public function destroy(Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.delete');
        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Devis supprimé.');
    }

    public function send(Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.send');
        if ($quote->status === 'brouillon') {
            $quote->generateClientToken();
            $quote->update(['status' => 'envoye']);

            // Send email to client with PDF attached
            if ($quote->client?->email) {
                try {
                    Mail::to($quote->client->email)->send(new QuoteSentMail($quote));
                } catch (\Throwable) {
                    // Email failure is non-blocking
                }
            }
        }

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis envoyé au client.');
    }

    public function accept(Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.accept');
        if (in_array($quote->status, ['brouillon', 'envoye'])) {
            DB::transaction(function () use ($quote) {
                $quote->update(['status' => 'accepte']);
                $this->activateAcceptedQuote($quote);
            });
        }

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis validé, chantier activé et tâches générées.');
    }

    public function convertToInvoice(Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.edit');
        $this->authorize('invoices.create');

        if ($quote->status !== 'accepte') {
            return back()->with('error', 'Seul un devis accepté peut être converti en facture.');
        }

        if (!$quote->project_id) {
            return back()->with('error', 'Ce devis n\'est lié à aucun chantier.');
        }

        try {
            $invoice = DB::transaction(fn () => $this->generateInvoiceFromQuote($quote));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Facture générée depuis le devis.');
    }

    /**
     * Crée la facture correspondant à un devis accepté (lignes + remise globale).
     * Utilisé aussi bien par la conversion manuelle (convertToInvoice) que par
     * la facturation automatique déclenchée à l'acceptation du devis
     * (activateAcceptedQuote). Lève une RuntimeException si la facture ne peut
     * pas être générée (déjà facturé, dépassement du montant du marché...).
     */
    private function generateInvoiceFromQuote(Quote $quote): Invoice
    {
        if (Invoice::where('quote_id', $quote->id)->exists()) {
            throw new \RuntimeException('Ce devis a déjà été converti en facture.');
        }

        $quote->loadMissing(['items', 'project']);

        // Garde anti-dépassement du marché : conversion directe + situations de
        // travaux ne doivent pas facturer plus que le montant contractuel.
        $contractAmount  = (float) $quote->project->contract_amount;
        $alreadyInvoiced = (float) Invoice::where('project_id', $quote->project_id)
            ->where('status', '!=', 'annulee')
            ->sum('total_ttc');

        if ($contractAmount > 0 && $alreadyInvoiced + (float) $quote->total_ttc > $contractAmount + 0.01) {
            throw new \RuntimeException(sprintf(
                'Conversion impossible : %s Ar déjà facturés sur ce chantier (situations comprises), facturer ce devis (%s Ar) dépasserait le montant du marché (%s Ar).',
                number_format($alreadyInvoiced, 0, ',', ' '),
                number_format((float) $quote->total_ttc, 0, ',', ' '),
                number_format($contractAmount, 0, ',', ' ')
            ));
        }

        $company = $quote->company;

        $rgRate   = (float) ($quote->project->rg_rate ?? 0);
        $rgAmount = round((float) $quote->total_ttc * $rgRate / 100, 2);
        $netToPay = $quote->total_ttc - $rgAmount;

        $invoice = $company->invoices()->create([
            'project_id'       => $quote->project_id,
            'client_id'        => $quote->client_id,
            'quote_id'         => $quote->id,
            'created_by'       => Auth::id() ?? $quote->created_by,
            'reference'        => $company->nextInvoiceReference(),
            'title'            => $quote->title,
            'type'             => 'standard',
            'invoice_date'     => now(),
            'due_date'         => now()->addDays(30),
            'tva_rate'         => $quote->tva_rate,
            'rg_rate'          => $rgRate,
            'subtotal_ht'      => $quote->taxable_ht,
            'tva_amount'       => $quote->tva_amount,
            'total_ttc'        => $quote->total_ttc,
            'rg_amount'        => $rgAmount,
            'net_to_pay'       => $netToPay,
            'amount_paid'      => 0,
            'amount_remaining' => $netToPay,
            'status'           => 'brouillon',
        ]);

        foreach ($quote->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'unit'        => $item->unit,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'total_ht'    => $item->total_ht,
                'sort_order'  => $item->sort_order,
            ]);
        }

        // La remise globale du devis devient une ligne négative : la somme des
        // lignes reste égale au sous-total HT de l'en-tête (taxable_ht), même
        // après un recalcul de la facture.
        if ((float) $quote->discount_amount > 0) {
            $invoice->items()->create([
                'description' => $quote->discount_type === 'percent'
                    ? "Remise globale ({$quote->discount_global}%)"
                    : 'Remise globale',
                'quantity'    => 1,
                'unit_price'  => -$quote->discount_amount,
                'total_ht'    => -$quote->discount_amount,
                'sort_order'  => ((int) $quote->items->max('sort_order')) + 1,
            ]);
        }

        \App\Models\ProjectLog::log(
            $quote->project_id,
            'quote_invoiced',
            "Le devis {$quote->reference} a été converti en facture {$invoice->reference}."
        );

        return $invoice;
    }

    public function generateTasks(Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.edit');

        if ($quote->status !== 'accepte') {
            return back()->with('error', 'Seul un devis accepté peut générer des tâches.');
        }

        if (!$quote->project_id) {
            return back()->with('error', 'Ce devis n\'est lié à aucun chantier.');
        }

        $count = $this->performTaskGeneration($quote);

        return redirect()->route('projects.show', ['project' => $quote->project_id, 'tab' => 'tasks'])
            ->with('success', "{$count} tâche(s) générée(s) avec succès.");
    }

    /**
     * Activation d'un devis accepté : naissance du chantier s'il n'existe pas
     * (montants initialisés depuis le devis), sinon cumul des montants du devis
     * au marché existant, puis génération des tâches. Appelé par accept() et
     * publicValidate(), toujours dans une transaction.
     */
    private function activateAcceptedQuote(Quote $quote, bool $viaPublicPortal = false): void
    {
        $quote->loadMissing('items');
        $dbeTotal = (float) $quote->items->sum('dbe_total');

        if (!$quote->project_id) {
            $project = Project::create([
                'company_id'      => $quote->company_id,
                'client_id'       => $quote->client_id,
                'name'            => $quote->title,
                'contract_amount' => $quote->total_ttc,
                'budget_total'    => $dbeTotal,
                'tva_rate'        => $quote->tva_rate,
                'status'          => 'en_cours',
                'start_date'      => now(),
            ]);

            $quote->setRelation('project', $project);
            $quote->update(['project_id' => $project->id]);

            $detail = 'Le chantier a été créé et activé';
        } else {
            // Chantier existant : le devis accepté s'ajoute au marché en cours
            $project     = $quote->project;
            $oldContract = (float) $project->contract_amount;
            $newContract = $oldContract + (float) $quote->total_ttc;

            $project->update([
                'status'          => 'en_cours',
                'contract_amount' => $newContract,
                'budget_total'    => (float) $project->budget_total + $dbeTotal,
            ]);

            $detail = sprintf(
                'Montant du marché porté de %s à %s Ar',
                number_format($oldContract, 0, ',', ' '),
                number_format($newContract, 0, ',', ' ')
            );
        }

        $this->performTaskGeneration($quote);

        \App\Models\ProjectLog::log(
            $quote->project_id,
            'quote_accepted',
            $viaPublicPortal
                ? "Le client a accepté le devis {$quote->reference} via le portail public. {$detail}, tâches générées."
                : "Le devis {$quote->reference} a été accepté. {$detail}, tâches générées."
        );

        // Facturation automatique : un devis accepté doit générer sa facture.
        // En cas d'impossibilité (garde anti-dépassement du marché...), on ne
        // bloque pas l'acceptation : la facturation reste possible manuellement
        // depuis la fiche du devis, et la raison est tracée dans les logs.
        try {
            $this->generateInvoiceFromQuote($quote);
        } catch (\RuntimeException $e) {
            \App\Models\ProjectLog::log(
                $quote->project_id,
                'quote_invoice_skipped',
                "Facturation automatique impossible pour le devis {$quote->reference} : {$e->getMessage()}"
            );
        }
    }

    /**
     * Logique interne de génération des tâches à partir des lignes du devis.
     */
    private function performTaskGeneration(Quote $quote): int
    {
        $project = $quote->project;
        if (!$project) return 0;

        $count = 0;
        foreach ($quote->items as $item) {
            // Éviter les doublons : une seule tâche par ligne de devis.
            // Le test sur le titre couvre les tâches créées avant l'existence de quote_item_id.
            $exists = $project->tasks()
                ->where(function ($q) use ($item) {
                    $q->where('quote_item_id', $item->id)
                      ->orWhere(fn ($q2) => $q2->whereNull('quote_item_id')->where('title', $item->description));
                })
                ->exists();
            if ($exists) continue;

            $project->tasks()->create([
                'company_id'    => $quote->company_id,
                'quote_item_id' => $item->id,
                'created_by'    => Auth::id() ?? $quote->created_by,
                'title'         => $item->description,
                'description'   => "Généré depuis le devis {$quote->reference}. Quantité prévue : {$item->quantity} {$item->unit}.",
                'status'        => 'a_faire',
                'priority'      => 'normale',
                'weight'        => 1,
                'due_date'      => $project->planned_end_date ?? now()->addDays(30),
            ]);
            $count++;
        }
        return $count;
    }

    public function addItem(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.edit');
        $request->validate([
            'description' => 'required|string',
            'quantity'    => 'required|numeric|min:0',
            'unit_price'  => 'required|numeric|min:0',
            'discount'    => 'nullable|numeric|min:0|max:100',
        ]);

        $lastOrder = $quote->items()->max('sort_order') ?? 0;
        $discountPct = (float) ($request->discount ?? 0);
        $totalHt = round($request->quantity * $request->unit_price * (1 - $discountPct / 100), 2);

        $quote->items()->create([
            'description'      => $request->description,
            'quantity'         => $request->quantity,
            'unit'             => $request->unit,
            'unit_price'       => $request->unit_price,
            'discount'         => $discountPct,
            'total_ht'         => $totalHt,
            'quote_section_id' => $request->quote_section_id ?: null,
            'sort_order'       => $lastOrder + 1,
        ]);

        $quote->recalculateTotals();

        return back()->with('success', 'Ligne ajoutée.');
    }

    public function updateItem(Request $request, Quote $quote, QuoteItem $item)
    {
        $this->authorizeQuote($quote, 'quotes.edit');
        abort_if($item->quote_id !== $quote->id, 403);

        $request->validate([
            'description' => 'required|string',
            'quantity'    => 'required|numeric|min:0',
            'unit_price'  => 'required|numeric|min:0',
            'discount'    => 'nullable|numeric|min:0|max:100',
        ]);

        $discountPct = (float) ($request->discount ?? 0);
        $totalHt = round($request->quantity * $request->unit_price * (1 - $discountPct / 100), 2);

        $item->update([
            'description'      => $request->description,
            'quantity'         => $request->quantity,
            'unit'             => $request->unit,
            'unit_price'       => $request->unit_price,
            'discount'         => $discountPct,
            'total_ht'         => $totalHt,
            'quote_section_id' => $request->quote_section_id ?: null,
        ]);

        $quote->recalculateTotals();

        return back()->with('success', 'Ligne mise à jour.');
    }

    public function removeItem(Quote $quote, QuoteItem $item)
    {
        $this->authorizeQuote($quote, 'quotes.edit');
        abort_if($item->quote_id !== $quote->id, 403);
        $item->delete();
        $quote->recalculateTotals();

        return back()->with('success', 'Ligne supprimée.');
    }

    public function refuse(Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.accept');
        abort_if(! in_array($quote->status, ['envoye', 'brouillon']), 422);
        $quote->update(['status' => 'refuse']);

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis marqué comme refusé.');
    }

    public function duplicate(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.create');
        $quote->load(['sections.items', 'items']);

        $company = Auth::user()->company;

        $newQuote = DB::transaction(function () use ($request, $quote, $company) {
            $newQuote = $quote->replicate(['client_token', 'client_responded_at', 'client_response_note', 'status', 'version', 'project_id']);
            $newQuote->reference        = $company->nextQuoteReference();
            $newQuote->title            = $request->title ?? ($quote->title . ' (copie)');
            $newQuote->quote_date       = now()->toDateString();
            $newQuote->status           = 'brouillon';
            $newQuote->version          = 1;
            $newQuote->parent_quote_id  = $quote->id;
            $newQuote->created_by       = Auth::id();
            $newQuote->save();

            // Copy sections and their items
            $sectionMap = [];
            foreach ($quote->sections as $section) {
                $newSection = $newQuote->sections()->create([
                    'title'      => $section->title,
                    'sort_order' => $section->sort_order,
                ]);
                $sectionMap[$section->id] = $newSection->id;
            }

            // Copy all items (respecting section mapping)
            foreach ($quote->items as $item) {
                $newItem = $item->replicate();
                $newItem->quote_id = $newQuote->id;
                $newItem->quote_section_id = $item->quote_section_id
                    ? ($sectionMap[$item->quote_section_id] ?? null)
                    : null;
                $newItem->save();
            }

            return $newQuote;
        });

        return redirect()->route('quotes.show', $newQuote)
            ->with('success', 'Devis dupliqué : ' . $newQuote->reference);
    }

    public function exportPdf(Quote $quote)
    {
        $this->authorizeQuote($quote);
        $quote->load(['project', 'client', 'sections.items', 'items', 'createdBy']);
        $company = Auth::user()->company;

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'company'))
            ->setPaper('A4', 'portrait');

        return $pdf->download($quote->reference . '.pdf');
    }

    public function addSection(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote, 'quotes.edit');
        abort_if($quote->status !== 'brouillon', 422);
        $request->validate(['title' => 'required|string|max:255']);

        $lastOrder = $quote->sections()->max('sort_order') ?? 0;
        $quote->sections()->create([
            'title'      => $request->title,
            'sort_order' => $lastOrder + 1,
        ]);

        return back()->with('success', 'Section ajoutée.');
    }

    public function removeSection(Quote $quote, QuoteSection $section)
    {
        $this->authorizeQuote($quote, 'quotes.edit');
        abort_if($section->quote_id !== $quote->id, 403);
        // Detach items (keep them as unsectioned)
        $section->items()->update(['quote_section_id' => null]);
        $section->delete();

        return back()->with('success', 'Section supprimée.');
    }

    public function publicValidation(string $token)
    {
        $quote = Quote::where('client_token', $token)
            ->with(['client', 'project', 'sections.items', 'items'])
            ->firstOrFail();

        return view('quotes.public', compact('quote', 'token'));
    }

    public function publicValidate(Request $request, string $token)
    {
        $quote = Quote::where('client_token', $token)->firstOrFail();

        abort_if(! in_array($quote->status, ['envoye']), 403);

        $request->validate([
            'decision' => 'required|in:accepte,refuse',
            'note'     => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($quote, $request) {
            $quote->update([
                'status'                => $request->decision,
                'client_responded_at'   => now(),
                'client_response_note'  => $request->note,
            ]);

            if ($request->decision === 'accepte') {
                $this->activateAcceptedQuote($quote, viaPublicPortal: true);

                // Notify the quote creator
                try {
                    $quote->createdBy?->notify(new QuoteAccepted($quote));
                } catch (\Throwable) {
                    // Notification failure is non-blocking
                }
            }
        });

        return redirect()->route('quotes.public', $token)
            ->with('success', $request->decision === 'accepte' ? 'Devis accepté et chantier activé. Merci !' : 'Devis refusé.');
    }

    private function authorizeQuote(Quote $quote, string $permission = 'quotes.view'): void
    {
        abort_if($quote->company_id !== Auth::user()->company_id, 403);
        $this->authorize($permission);
    }
}
