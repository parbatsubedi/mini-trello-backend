<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            // 'user_id' => 'required|exists:users,id',
            // 'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,on_hold,completed,archived',
            'visibility' => 'required|in:open,closed',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'members' => 'required|array',
            'members.*' => 'exists:users,id',
            'client_id' => 'nullable|exists:clients,id',
            'project_type' => 'required|string',
            'price' => 'required|numeric',
            'labels' => 'required|array',
            'labels.*' => 'exists:labels,id',
        ];
    }
}
