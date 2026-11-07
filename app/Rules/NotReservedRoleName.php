<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class NotReservedRoleName implements ValidationRule
{
    /**
     * Role names reserved for the superadmin flag (managed by `rbac:superadmin`).
     *
     * @var list<string>
     */
    private const array RESERVED = [
        'superadmin',
        'super admin',
        'super-admin',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (in_array(Str::lower(Str::squish($value)), self::RESERVED, true)) {
            $fail('The superadmin role is managed by the rbac:superadmin command and cannot be created.');
        }
    }
}
