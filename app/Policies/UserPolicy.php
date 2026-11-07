<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksResourcePermissions;

class UserPolicy
{
    use ChecksResourcePermissions;

    public function update(User $user, mixed $model = null): bool
    {
        if (! $model instanceof User) {
            return false;
        }

        if (! config('rbac.enabled')) {
            return true;
        }

        return ! $model->isSuperAdmin() && $this->allows($user, 'update');
    }

    public function delete(User $user, mixed $model = null): bool
    {
        if (! $model instanceof User) {
            return false;
        }

        if (! config('rbac.enabled')) {
            return true;
        }

        return ! $user->is($model)
            && ! $model->isSuperAdmin()
            && $this->allows($user, 'delete');
    }

    protected function resource(): string
    {
        return 'user';
    }
}
