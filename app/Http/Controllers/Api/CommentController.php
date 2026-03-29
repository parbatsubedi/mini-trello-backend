<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $comments = $this->service->paginate($perPage, ['*'], ['user']);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $comment = $this->service->create($request->validated());

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $comment = $this->service->findOrFail($id, ['*'], ['user']);

        return (new CommentResource($comment))->response();
    }

    public function update(UpdateCommentRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());

        return (new CommentResource($this->service->findOrFail($id)))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    public function getByTask(int $taskId): AnonymousResourceCollection
    {
        $comments = $this->service->getByTask($taskId);

        return CommentResource::collection($comments);
    }
}
