<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class RolePolicy
{
    use ChecksResourcePermissions;

    protected function resource(): string
    {
        return 'role';
    }
}
