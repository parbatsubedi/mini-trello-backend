<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Services\AttachmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AttachmentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $attachments = $this->service->paginate($perPage, ['*'], ['user']);

            return $this->paginatedResponse($attachments, 'Attachments fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch attachments: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $attachment = $this->service->create($data, $request->file('file'));

            return $this->successResponse(new AttachmentResource($attachment), 'Attachment created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create attachment: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $attachment = $this->service->findOrFail($id, ['*'], ['user']);

            return $this->successResponse(new AttachmentResource($attachment), 'Attachment fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch attachment: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->successResponse(null, 'Attachment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete attachment: '.$e->getMessage(), 500);
        }
    }

    public function getByTask(int $taskId): JsonResponse
    {
        try {
            $attachments = $this->service->getByTask($taskId);

            return $this->successResponse(AttachmentResource::collection($attachments), 'Attachments fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch attachments: '.$e->getMessage(), 500);
        }
    }
}
