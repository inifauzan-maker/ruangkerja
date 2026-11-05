<?php

namespace App\Http\Requests\Admin;

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
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->route('user'))],
            'job_title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s.]+$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'global_role' => ['required', Rule::in(User::globalRoles())],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'global_role.required' => 'Role global wajib dipilih.',
            'global_role.in' => 'Role global tidak valid.',
            'is_active.required' => 'Status akun wajib ditentukan.',
        ];
    }
}
