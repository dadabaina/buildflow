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
        $prefix  = $company->quote_prefix ?? 'DEV';
        $lastNum = $company->quotes()->max(DB::raw("CAST(SUBSTRING(reference, LENGTH('{$prefix}')+2) AS UNSIGNED)")) ?? 0;
        $reference = $prefix . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

        $quote = $company->quotes()->create([
            'project_id'      => $request->project_id,
            'client_id'       => $request->client_id,
            'created_by'      => Auth::id(),
            'reference'       => $reference,
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
        ]);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Devis créé. Ajoutez maintenant les lignes.');
    }

    public function show(Quote $quote)
    {
        $this->authorizeQuote($quote);
        $quote->load(['project', 'client', 'sections.items', 'items', 'createdBy']);
        $company      = Auth::user()->company;
        $unitTypes    = $company->unitTypes()->where('is_active', true)->orderBy('name')->get();
        $dosageModels = $company->dosageModels()->where('is_active', true)->orderBy('name')->get();

        return view('quotes.show', compact('quote', 'unitTypes', 'dosageModels', 'company'));
    }

    public function edit(Quote $quote)
    {
        $this->authorizeQuote($quote);
        $company = Auth::user()->company;
        $projects = $company->projects()->orderBy('name')->get();
        $clients  = $company->clients()->orderBy('name')->get();
        $selectedProject = $quote->project;

        return view('quotes.form', compact('quote', 'projects', 'clients', 'selectedProject'));
    }

    public function update(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote);
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
        $this->authorizeQuote($quote);
        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Devis supprimé.');
    }

    public function send(Quote $quote)
    {
        $this->authorizeQuote($quote);
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
        $this->authorizeQuote($quote);
        if (in_array($quote->status, ['brouillon', 'envoye'])) {
            DB::transaction(function () use ($quote) {
                $quote->update(['status' => 'accepte']);

                // Naissance du chantier si non lié
                if (!$quote->project_id) {
                    $project = Project::create([
                        'company_id'      => $quote->company_id,
                        'client_id'       => $quote->client_id,
                        'name'            => $quote->title,
                        'contract_amount' => $quote->subtotal_ht,
                        'tva_rate'        => $quote->tva_rate,
                        'status'          => 'en_cours',
                        'start_date'      => now(),
                    ]);

                    $quote->setRelation('project', $project);
                    $quote->update(['project_id' => $project->id]);
                } else {
                    // Si déjà lié, on s'assure que le projet passe en cours
                    $quote->project->update(['status' => 'en_cours']);
                }

                // Génération automatique des tâches
                $this->performTaskGeneration($quote);

                \App\Models\ProjectLog::log(
                    $quote->project_id,
                    'quote_accepted',
                    "Le devis {$quote->reference} a été accepté. Le chantier a été activé et les tâches générées."
                );
            });
        }

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis validé, chantier activé et tâches générées.');
    }

    public function convertToInvoice(Quote $quote)
    {
        // ... (existing code)
    }

    public function generateTasks(Quote $quote)
    {
        $this->authorizeQuote($quote);

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
     * Logique interne de génération des tâches à partir des lignes du devis.
     */
    private function performTaskGeneration(Quote $quote): int
    {
        $project = $quote->project;
        if (!$project) return 0;

        $count = 0;
        foreach ($quote->items as $item) {
            // Éviter les doublons basés sur le titre
            $exists = $project->tasks()->where('title', $item->description)->exists();
            if ($exists) continue;

            $project->tasks()->create([
                'company_id'  => $quote->company_id,
                'created_by'  => Auth::id() ?? $quote->created_by,
                'title'       => $item->description,
                'description' => "Généré depuis le devis {$quote->reference}. Quantité prévue : {$item->quantity} {$item->unit}.",
                'status'      => 'a_faire',
                'priority'    => 'normale',
                'weight'      => 1,
                'due_date'    => $project->planned_end_date ?? now()->addDays(30),
            ]);
            $count++;
        }
        return $count;
    }

    public function addItem(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote);
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
        $this->authorizeQuote($quote);
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
        $this->authorizeQuote($quote);
        abort_if($item->quote_id !== $quote->id, 403);
        $item->delete();
        $quote->recalculateTotals();

        return back()->with('success', 'Ligne supprimée.');
    }

    public function refuse(Quote $quote)
    {
        $this->authorizeQuote($quote);
        abort_if(! in_array($quote->status, ['envoye', 'brouillon']), 422);
        $quote->update(['status' => 'refuse']);

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis marqué comme refusé.');
    }

    public function duplicate(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote);
        $quote->load(['sections.items', 'items']);

        $company = Auth::user()->company;
        $prefix  = $company->quote_prefix ?? 'DEV';
        $lastNum = $company->quotes()->max(DB::raw("CAST(SUBSTRING(reference, LENGTH('{$prefix}')+2) AS UNSIGNED)")) ?? 0;
        $reference = $prefix . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

        $newQuote = $quote->replicate(['client_token', 'client_responded_at', 'client_response_note', 'status', 'version', 'project_id']);
        $newQuote->reference        = $reference;
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
        $this->authorizeQuote($quote);
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
        $this->authorizeQuote($quote);
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
                // Naissance du chantier si non lié
                if (!$quote->project_id) {
                    $project = Project::create([
                        'company_id'      => $quote->company_id,
                        'client_id'       => $quote->client_id,
                        'name'            => $quote->title,
                        'contract_amount' => $quote->subtotal_ht,
                        'tva_rate'        => $quote->tva_rate,
                        'status'          => 'en_cours',
                        'start_date'      => now(),
                    ]);

                    $quote->update(['project_id' => $project->id]);
                    $quote->setRelation('project', $project);
                } else {
                    // Si déjà lié, on s'assure que le projet passe en cours
                    $quote->project->update(['status' => 'en_cours']);
                }

                // Génération automatique des tâches
                $this->performTaskGeneration($quote);

                \App\Models\ProjectLog::log(
                    $quote->project_id,
                    'quote_accepted',
                    "Le client a accepté le devis {$quote->reference} via le portail public. Le chantier a été activé."
                );

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

    private function authorizeQuote(Quote $quote): void
    {
        abort_if($quote->company_id !== Auth::user()->company_id, 403);
    }
}
