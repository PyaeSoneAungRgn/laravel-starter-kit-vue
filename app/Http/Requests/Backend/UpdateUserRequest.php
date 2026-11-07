<?php

namespace App\Http\Requests\Backend;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User && (bool) $this->user()?->can('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guard = (string) config('auth.defaults.guard', 'web');
        $user = $this->route('user');

        $emailUnique = Rule::unique(User::class);

        if ($user instanceof User) {
            $emailUnique->ignore($user->id);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                $emailUnique,
            ],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
            'roles' => ['array'],
            'roles.*' => [
                'string',
                Rule::exists((string) config('permission.table_names.roles'), 'name')->where('guard_name', $guard),
            ],
        ];
    }
}
