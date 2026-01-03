<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class AdminRolePermissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of roles.
     */
    public function rolesIndex()
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        $allPermissions = Permission::orderBy('name')->get();

        return view('admin.roles.index', compact('roles', 'allPermissions'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function rolesCreate()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            // Grouper par préfixe (ex: view-, create-, edit-)
            $parts = explode('-', $permission->name);
            return $parts[0] ?? 'other';
        });

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function rolesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->filled('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function rolesEdit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return $parts[0] ?? 'other';
        });

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function rolesUpdate(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle modifié avec succès.');
    }

    /**
     * Remove the specified role.
     */
    public function rolesDestroy($id)
    {
        $role = Role::findOrFail($id);

        // Vérifier si le rôle est utilisé
        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => 'Ce rôle ne peut pas être supprimé car il est assigné à des utilisateurs.']);
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }

    /**
     * Display a listing of permissions.
     */
    public function permissionsIndex()
    {
        $permissions = Permission::with('roles')->orderBy('name')->get()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return $parts[0] ?? 'other';
        });

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show role details with users.
     */
    public function rolesShow($id)
    {
        $role = Role::with(['users', 'permissions'])->findOrFail($id);
        $allPermissions = Permission::orderBy('name')->get();

        return view('admin.roles.show', compact('role', 'allPermissions'));
    }
}

