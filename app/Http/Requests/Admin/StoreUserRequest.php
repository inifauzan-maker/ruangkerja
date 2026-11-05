<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)],
            'job_title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s.]+$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'global_role' => ['required', Rule::in(User::globalRoles())],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
