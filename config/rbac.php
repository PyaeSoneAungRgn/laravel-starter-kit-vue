<?php

use Spatie\Permission\Models\Role;

return [

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions
    |--------------------------------------------------------------------------
    |
    | Setting this to false disables the role and permission feature: the
    | management screens are removed (routes return 404 and the navigation
    | links disappear) and permission checks are skipped for authenticated
    | users. The tables are left in place so the feature can be enabled again
    | without migrating.
    |
    */

    'enabled' => env('RBAC_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Generation Abilities
    |--------------------------------------------------------------------------
    |
    | The policy abilities converted into permissions by `rbac:sync`. Every
    | ability becomes "<ability>_<resource>", e.g. "view_any_product", and is
    | filtered per model: restore/forceDelete are only generated for models
    | that use SoftDeletes.
    |
    */

    'abilities' => [
        'viewAny',
        'view',
        'create',
        'update',
        'delete',
        'restore',
        'forceDelete',
    ],

    /*
    |--------------------------------------------------------------------------
    | Discovered Models
    |--------------------------------------------------------------------------
    |
    | Resources are discovered from app/Models and every module's src/Models
    | directory. Models that do not live in those directories can be added
    | below; classes listed in "exclude" are skipped.
    |
    */

    'models' => [
        'include' => [
            Role::class,
        ],

        'exclude' => [],
    ],

];
