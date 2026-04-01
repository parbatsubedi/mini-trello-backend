<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
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

            $labels = $query->get();

            return $this->successResponse(LabelResource::collection($labels), 'Labels fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch labels: '.$e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'color' => 'nullable|string|max:255',
                'type' => 'nullable|in:project,task,both',
            ]);

            $label = Label::create($validated);

            return $this->successResponse(new LabelResource($label), 'Label created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create label: '.$e->getMessage(), 500);
        }
    }

    public function show(Label $label): JsonResponse
    {
        try {
            return $this->successResponse(new LabelResource($label), 'Label fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch label: '.$e->getMessage(), 500);
        }
    }

    public function update(Request $request, Label $label): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'color' => 'nullable|string|max:255',
                'type' => 'nullable|in:project,task,both',
            ]);

            $label->update($validated);

            return $this->successResponse(new LabelResource($label), 'Label updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update label: '.$e->getMessage(), 500);
        }
    }

    public function destroy(Label $label): JsonResponse
    {
        try {
            $label->delete();

            return $this->successResponse(null, 'Label deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete label: '.$e->getMessage(), 500);
        }
    }
}
