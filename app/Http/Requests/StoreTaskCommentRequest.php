<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:3000'],
            'mention_ids' => ['nullable', 'array', 'max:50'],
            'mention_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }
}
