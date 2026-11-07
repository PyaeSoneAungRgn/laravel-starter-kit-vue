<?php

namespace App\Support\Rbac;

use Illuminate\Support\Str;

final class PermissionName
{
    /**
     * Build a Shield-style permission name, e.g. "view_any_product".
     */
    public static function make(string $resource, string $ability): string
    {
        return Str::snake($ability) . '_' . $resource;
    }
}
