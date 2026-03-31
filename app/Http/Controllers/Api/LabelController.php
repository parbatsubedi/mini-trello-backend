<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index(Request $request)
    {
        $query = Label::query();

        $types = $request->input('type');

        if ($types) {
            if (is_array($types)) {
                $query->whereIn('type', $types);
            } else {
                if (in_array($types, ['project', 'task', 'both'])) {
                    $query->where('type', $types);
                }
            }
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
            'type' => 'nullable|in:project,task,both',
        ]);

        $label = Label::create($validated);

        return response()->json($label, 201);
    }

    public function show(Label $label)
    {
        return response()->json($label);
    }

    public function update(Request $request, Label $label)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:255',
            'type' => 'nullable|in:project,task,both',
        ]);

        $label->update($validated);

        return response()->json($label);
    }

    public function destroy(Label $label)
    {
        $label->delete();

        return response()->json(null, 204);
    }
}
