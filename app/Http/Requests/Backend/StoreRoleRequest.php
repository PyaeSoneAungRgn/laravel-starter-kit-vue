<?php

namespace App\Http\Requests\Backend;

use App\Rules\NotReservedRoleName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Role::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guard = (string) config('auth.defaults.guard', 'web');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class)->where('guard_name', $guard),
                new NotReservedRoleName,
            ],
            'permissions' => ['array'],
            'permissions.*' => [
                'string',
                Rule::exists((string) config('permission.table_names.permissions'), 'name')->where('guard_name', $guard),
            ],
        ];
    }
}
