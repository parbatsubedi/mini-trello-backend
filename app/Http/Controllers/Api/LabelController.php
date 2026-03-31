<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index()
    {
        return response()->json(Label::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
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
