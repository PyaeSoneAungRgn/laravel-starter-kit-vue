<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreRoleRequest;
use App\Http\Requests\Backend\UpdateRoleRequest;
use App\Support\Rbac\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly PermissionCatalog $permissionCatalog,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->paginate(15)
            ->through(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => (int) $role->users_count,
                'permissions_count' => (int) $role->permissions_count,
                'created_at' => $role->created_at?->toIso8601String(),
            ]);

        return Inertia::render('roles/Index', ['roles' => $roles]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Role::class);

        return Inertia::render('roles/Create', [
            'permissionGroups' => $this->permissionCatalog->grouped(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $role = Role::create([
            'name' => (string) $request->validated('name'),
            'guard_name' => (string) config('auth.defaults.guard', 'web'),
        ]);

        $role->syncPermissions((array) $request->validated('permissions'));

        return to_route('roles.index')->with('status', 'role-created');
    }

    public function edit(Role $role): Response
    {
        Gate::authorize('update', $role);

        $role->loadMissing('permissions');

        return Inertia::render('roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->all(),
            ],
            'permissionGroups' => $this->permissionCatalog->grouped(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);

        $role->update(['name' => (string) $request->validated('name')]);
        $role->syncPermissions((array) $request->validated('permissions'));

        return to_route('roles.index')->with('status', 'role-updated');
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'This role is still assigned to users.']);
        }

        $role->delete();

        return to_route('roles.index')->with('status', 'role-deleted');
    }
}
