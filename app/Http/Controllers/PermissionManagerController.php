<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class PermissionManagerController extends Controller
{
    public function index()
    {
        $users = User::with('roles', 'permissions')->get();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('admin.permissions.index', compact('users', 'roles', 'permissions'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $permissions = Permission::orderBy('name')->get();
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        $userRoles = $user->getRoleNames()->toArray();

        // Grupo permissions sipas modulit
        $groupedPermissions = $permissions->groupBy(function ($p) {
            $parts = explode(' ', $p->name);
            return $parts[1] ?? $p->name;
        });

        return view('admin.permissions.edit', compact('user', 'roles', 'groupedPermissions', 'userPermissions', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles'       => 'nullable|array',
            'roles.*'     => 'exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Sync roles
        $user->syncRoles($request->roles ?? []);

        // Sync direct permissions
        $user->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Lejet e {$user->name} u përditësuan me sukses!");
    }
}
