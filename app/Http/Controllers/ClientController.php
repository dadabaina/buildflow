<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Region;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $company = auth()->user()->company;
        $query = $company->clients()->with('region')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($regionId = $request->input('region_id')) {
            $query->where('region_id', $regionId);
        }
        if ($request->input('archived')) {
            $query->onlyTrashed();
        }

        $clients = $query->paginate(20)->withQueryString();
        $regions = $company->regions()->orderBy('name')->get();

        $stats = [
            'total_count'    => $company->clients()->count(),
            'active_count'   => $company->clients()->where('is_active', true)->count(),
            'by_type'        => $company->clients()->selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type'),
        ];

        return view('clients.index', compact('clients', 'regions', 'stats'));
    }

    public function create()
    {
        $regions = Region::orderBy('name')->get();
        return view('clients.form', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateClient($request);
        Client::create($validated);
        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    public function show(Client $client)
    {
        $client->load([
            'region',
            'projects' => fn($q) => $q->latest()->take(10),
            'quotes' => fn($q) => $q->with('project')->latest()->take(10),
            'invoices' => fn($q) => $q->latest()->take(10),
            'payments' => fn($q) => $q->with('project')->latest()->take(10),
        ]);

        $stats = [
            'projects_count' => $client->projects()->count(),
            'total_quotes'   => $client->quotes()->where('status', 'accepte')->sum('total_ttc'),
            'total_invoiced' => $client->invoices()->where('status', '!=', 'annulee')->sum('total_ttc'),
            'total_paid'     => $client->payments()->sum('amount'),
        ];
        $stats['balance'] = $stats['total_invoiced'] - $stats['total_paid'];

        return view('clients.show', compact('client', 'stats'));
    }

    public function edit(Client $client)
    {
        $regions = Region::orderBy('name')->get();
        return view('clients.form', compact('client', 'regions'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $this->validateClient($request, $client->id);
        $client->update($validated);
        return redirect()->route('clients.show', $client)
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')
            ->with('success', 'Client archivé.');
    }

    public function restore(int $id)
    {
        $client = Client::withTrashed()->findOrFail($id);
        $client->restore();
        return redirect()->route('clients.index')
            ->with('success', 'Client restauré.');
    }

    private function validateClient(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:191'],
            'type'       => ['required', 'in:particulier,entreprise,administration'],
            'email'      => ['nullable', 'email', 'max:191'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'address'    => ['nullable', 'string', 'max:500'],
            'city'       => ['nullable', 'string', 'max:100'],
            'region_id'  => ['nullable', 'exists:regions,id'],
            'nif'        => ['nullable', 'string', 'max:30'],
            'stat'       => ['nullable', 'string', 'max:30'],
            'rcs'        => ['nullable', 'string', 'max:30'],
            'notes'      => ['nullable', 'string'],
        ]);
    }
}
