<?php

namespace App\Http\Requests;

use App\Models\Label;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:todo,in_progress,review,done',
            'due_date' => 'nullable|date',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'points' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'is_recurring' => 'nullable|boolean',
            'labels' => 'nullable|array',
            'labels.*' => [
                'exists:labels,id',
                function ($attribute, $value, $fail) {
                    $label = Label::find($value);
                    if ($label && ! in_array($label->type, ['task', 'both'])) {
                        $fail("Label with id $value is not valid for tasks.");
                    }
                },
            ],
        ];
    }
}
