<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('users.view');
        $company = Auth::user()->company;
        $users   = $userQuery = $company->users()->with('roles')->orderBy('name')->get();
        $roles   = Role::where('company_id', $company->id)->orWhereNull('company_id')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->authorize('users.create');
        $roles    = Role::where('company_id', Auth::user()->company_id)->orWhereNull('company_id')->get();
        $projects = Auth::user()->company->projects()->orderBy('name')->get();
        return view('users.form', compact('roles', 'projects'));
    }

    public function store(Request $request)
    {
        $this->authorize('users.create');
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'role'         => 'required|exists:roles,name',
            'project_ids'   => ['nullable', 'array'],
            'project_ids.*' => ['exists:projects,id'],
        ]);

        $company = Auth::user()->company;
        $user = $company->users()->create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'company_id' => $company->id,
        ]);

        // Spatie teams handles company_id via setPermissionsTeamId in middleware
        $user->assignRole($request->role);

        if ($request->role === 'chef_chantier') {
            $user->managedProjects()->sync($request->input('project_ids', []));
        }

        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user)
    {
        $this->authorize('users.edit');
        abort_if($user->company_id !== Auth::user()->company_id, 403);
        $roles    = Role::where('company_id', Auth::user()->company_id)->orWhereNull('company_id')->get();
        $projects = Auth::user()->company->projects()->orderBy('name')->get();
        $managedProjectIds = $user->managedProjects()->pluck('projects.id')->toArray();
        return view('users.form', compact('user', 'roles', 'projects', 'managedProjectIds'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('users.edit');
        abort_if($user->company_id !== Auth::user()->company_id, 403);
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:8|confirmed',
            'role'          => 'required|exists:roles,name',
            'project_ids'   => ['nullable', 'array'],
            'project_ids.*' => ['exists:projects,id'],
        ]);

        $data = ['name' => $request->name, 'email' => $request->email];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);
        $user->managedProjects()->sync($request->role === 'chef_chantier' ? $request->input('project_ids', []) : []);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        $this->authorize('users.delete');
        abort_if($user->company_id !== Auth::user()->company_id, 403);
        abort_if($user->id === Auth::id(), 403, 'Vous ne pouvez pas supprimer votre propre compte.');

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}
