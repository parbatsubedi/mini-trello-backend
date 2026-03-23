<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagController extends Controller
{
    public function __construct(
        protected TagService $service
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $tags = $this->service->all(['*'], ['tasks']);

        return TagResource::collection($tags);
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->service->create($request->validated());

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $tag = $this->service->findOrFail($id, ['*'], ['tasks']);

        return (new TagResource($tag))->response();
    }

    public function update(UpdateTagRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());

        return (new TagResource($this->service->findOrFail($id)))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Tag deleted successfully']);
    }
}
