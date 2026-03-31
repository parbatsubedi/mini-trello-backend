<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|required|exists:users,id',
            // 'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,on_hold,completed,archived',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'members' => 'required|array',
            'members.*' => 'exists:users,id',
            'client_id' => 'nullable|exists:clients,id',
            'project_type' => 'nullable|string',
            'price' => 'nullable|numeric',
            'labels' => 'nullable|array',
            'labels.*' => 'exists:labels,id',
        ];
    }
}
