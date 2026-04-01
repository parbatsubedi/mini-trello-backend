<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CommentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $comments = $this->service->paginate($perPage, ['*'], ['user']);

            return $this->paginatedResponse($comments, 'Comments fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch comments: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        try {
            $comment = $this->service->create($request->validated());

            return $this->successResponse(new CommentResource($comment), 'Comment created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create comment: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $comment = $this->service->findOrFail($id, ['*'], ['user']);

            return $this->successResponse(new CommentResource($comment), 'Comment fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch comment: '.$e->getMessage(), 500);
        }
    }

    public function update(UpdateCommentRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->update($id, $request->validated());
            $comment = $this->service->findOrFail($id);

            return $this->successResponse(new CommentResource($comment), 'Comment updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update comment: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->successResponse(null, 'Comment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete comment: '.$e->getMessage(), 500);
        }
    }

    public function getByTask(int $taskId): JsonResponse
    {
        try {
            $comments = $this->service->getByTask($taskId);

            return $this->successResponse(CommentResource::collection($comments), 'Comments fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch comments: '.$e->getMessage(), 500);
        }
    }
}
