<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use App\Notifications\DocumentUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $isChefChantier = Auth::user()->hasRole('chef_chantier');
        $managedProjectIds = $isChefChantier ? Auth::user()->managedProjects()->pluck('projects.id') : null;

        $query = Document::with(['project', 'uploadedBy'])->latest();
        if ($isChefChantier) {
            $query->whereIn('project_id', $managedProjectIds);
        }

        if ($projectId = $request->input('project_id')) {
            $this->authorizeProjectScope((int) $projectId);
            $query->where('project_id', $projectId);
        }
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }
        if ($search = $request->input('search')) {
            $query->where('original_name', 'like', "%{$search}%");
        }

        $documents  = $query->paginate(25)->withQueryString();
        $projects   = $isChefChantier
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();
        $categories = Document::$categories;

        $statsBase = $isChefChantier ? Document::whereIn('project_id', $managedProjectIds) : Document::query();
        $stats = [
            'total_count' => (clone $statsBase)->count(),
            'total_size'  => (clone $statsBase)->sum('file_size'),
            'by_category' => (clone $statsBase)->selectRaw('category, count(*) as count')->groupBy('category')->get()->pluck('count', 'category'),
        ];

        return view('documents.index', compact('documents', 'projects', 'categories', 'stats'));
    }

    public function create(Request $request)
    {
        $projects   = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();
        $categories = Document::$categories;
        $selected   = $request->input('project_id');
        return view('documents.form', compact('projects', 'categories', 'selected'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'category'   => ['required', 'string'],
            'file'       => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,dwg,zip,rar'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->input('project_id')) {
            $this->authorizeProjectScope((int) $request->input('project_id'));
        }

        $file       = $request->file('file');
        $storedName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path       = $file->storeAs('documents/' . Auth::user()->company_id, $storedName, 'private');

        $document = Document::create([
            'company_id'    => Auth::user()->company_id,
            'project_id'    => $request->input('project_id'),
            'uploaded_by'   => Auth::id(),
            'category'      => $request->input('category', 'autre'),
            'original_name' => $file->getClientOriginalName(),
            'stored_name'   => $storedName,
            'path'          => $path,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'notes'         => $request->input('notes'),
        ]);

        // Notify company admins
        try {
            $admins = User::role('admin')
                ->where('company_id', Auth::user()->company_id)
                ->where('id', '!=', Auth::id()) // Don't notify the person who uploaded it
                ->get();
            
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new DocumentUploaded($document));
            }
        } catch (\Throwable $e) {
            // Notification failure should not block the upload process
        }

        return redirect()->route('documents.index', ['project_id' => $request->input('project_id')])
            ->with('success', 'Document ajouté : ' . $file->getClientOriginalName());
    }

    public function show(Document $document)
    {
        $this->authorizeDocument($document);

        if (!Storage::disk('private')->exists($document->path)) {
            abort(404, 'Fichier introuvable.');
        }

        return Storage::disk('private')->response($document->path, $document->original_name);
    }

    public function destroy(Document $document)
    {
        $this->authorizeDocument($document);

        Storage::disk('private')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    private function authorizeDocument(Document $document): void
    {
        if ($document->company_id !== Auth::user()->company_id) {
            abort(403);
        }
        if ($document->project_id) {
            $this->authorizeProjectScope($document->project_id);
        }
    }
}
