<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:roles,slug,'.$this->route('role'),
            'description' => 'nullable|string',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ];
    }
}
