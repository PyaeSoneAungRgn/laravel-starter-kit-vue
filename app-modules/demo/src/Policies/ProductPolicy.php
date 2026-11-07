<?php

namespace Modules\Demo\Policies;

use App\Policies\Concerns\ChecksResourcePermissions;

class ProductPolicy
{
    use ChecksResourcePermissions;

    protected function resource(): string
    {
        return 'product';
    }
}
