<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\Rbac\PermissionName;

trait ChecksResourcePermissions
{
    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'viewAny');
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, mixed $model = null): bool
    {
        return $this->allows($user, 'restore');
    }

    public function forceDelete(User $user, mixed $model = null): bool
    {
        return $this->allows($user, 'forceDelete');
    }

    abstract protected function resource(): string;

    protected function allows(User $user, string $ability): bool
    {
        if (! config('rbac.enabled')) {
            return true;
        }

        return $user->can(PermissionName::make($this->resource(), $ability));
    }
}
