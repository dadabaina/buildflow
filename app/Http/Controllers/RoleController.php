<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('roles.view');
        $companyId = Auth::user()->company_id;
        // Spatie with teams should handle this, but let's be explicit
        $roles = Role::where('company_id', $companyId)->get();
        
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('roles.create');
        $permissions = Permission::all()->groupBy(function($perm) {
            return explode('.', $perm->name)[0];
        });
        
        return view('roles.form', compact('permissions'));
    }

    public function store(Request $request)
    {
        $this->authorize('roles.create');
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
        ]);

        $companyId = Auth::user()->company_id;
        
        // Create role for this company
        $role = Role::create([
            'name' => $request->name,
            'company_id' => $companyId,
            'guard_name' => 'web'
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Rôle créé avec succès.');
    }

    public function edit(Role $role)
    {
        $this->authorize('roles.edit');
        abort_if($role->company_id !== Auth::user()->company_id, 403);
        
        $permissions = Permission::all()->groupBy(function($perm) {
            return explode('.', $perm->name)[0];
        });
        
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('roles.form', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('roles.edit');
        abort_if($role->company_id !== Auth::user()->company_id, 403);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Rôle mis à jour.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('roles.delete');
        abort_if($role->company_id !== Auth::user()->company_id, 403);
        
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un rôle assigné à des utilisateurs.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rôle supprimé.');
    }
}
