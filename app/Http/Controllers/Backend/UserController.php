<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreUserRequest;
use App\Http\Requests\Backend\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $search = trim((string) $request->string('search'));

        $users = User::query()
            ->with('roles')
            ->unless($request->user()?->isSuperAdmin(), fn (Builder $query): Builder => $query->where('is_superadmin', false))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_superadmin' => $user->isSuperAdmin(),
                'roles' => $user->roles->pluck('name')->all(),
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('users/Create', [
            'roles' => $this->roleNames(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = new User;
        $user->fill($request->safe()->only(['name', 'email', 'password']));
        $user->forceFill(['email_verified_at' => now()]);
        $user->save();
        $user->syncRoles((array) $request->validated('roles'));

        return to_route('users.index')->with('status', 'user-created');
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        $user->loadMissing('roles');

        return Inertia::render('users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_superadmin' => $user->isSuperAdmin(),
                'roles' => $user->roles->pluck('name')->all(),
            ],
            'roles' => $this->roleNames(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $user->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->password = (string) $request->validated('password');
        }

        $user->save();
        $user->syncRoles((array) $request->validated('roles'));

        return to_route('users.index')->with('status', 'user-updated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        Gate::authorize('delete', $user);

        $user->delete();

        return to_route('users.index')->with('status', 'user-deleted');
    }

    /**
     * @return list<string>
     */
    private function roleNames(): array
    {
        return Role::query()
            ->where('guard_name', (string) config('auth.defaults.guard', 'web'))
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
