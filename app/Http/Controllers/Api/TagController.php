<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Services\TagService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TagService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $tags = $this->service->paginate($perPage, ['*'], ['tasks']);

            return $this->paginatedResponse($tags, 'Tags fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch tags: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        try {
            $tag = $this->service->create($request->validated());

            return $this->successResponse(new TagResource($tag), 'Tag created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create tag: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $tag = $this->service->findOrFail($id, ['*'], ['tasks']);

            return $this->successResponse(new TagResource($tag), 'Tag fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch tag: '.$e->getMessage(), 500);
        }
    }

    public function update(UpdateTagRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->update($id, $request->validated());
            $tag = $this->service->findOrFail($id);

            return $this->successResponse(new TagResource($tag), 'Tag updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update tag: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->successResponse(null, 'Tag deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete tag: '.$e->getMessage(), 500);
        }
    }
}
