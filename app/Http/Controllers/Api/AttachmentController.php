<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttachmentController extends Controller
{
    public function __construct(
        protected AttachmentService $service
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $attachments = $this->service->all(['*'], ['user']);

        return AttachmentResource::collection($attachments);
    }

    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $attachment = $this->service->create($data, $request->file('file'));

        return (new AttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $attachment = $this->service->findOrFail($id, ['*'], ['user']);

        return (new AttachmentResource($attachment))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Attachment deleted successfully']);
    }

    public function getByTask(int $taskId): AnonymousResourceCollection
    {
        $attachments = $this->service->getByTask($taskId);

        return AttachmentResource::collection($attachments);
    }
}
