<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProjectService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $relations = ['creator', 'department', 'members', 'client', 'labels', 'tasks'];
            $projects = $this->service->paginate($perPage, ['*'], $relations);

            return $this->paginatedResponse($projects, 'Projects fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch projects: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $project = $this->service->create($request->validated());

            return $this->successResponse(new ProjectResource($project), 'Project created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create project: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $project = $this->service->findOrFail($id, ['*'], ['creator', 'department', 'members', 'tasks', 'client', 'labels', 'tasks.assignee', 'tasks.tags', 'tasks.attachments', 'tasks.collaborators', 'tasks.comments']);

            return $this->successResponse(new ProjectResource($project), 'Project fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch project: '.$e->getMessage(), 500);
        }
    }

    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->update($id, $request->validated());
            $project = $this->service->findOrFail($id);

            return $this->successResponse(new ProjectResource($project), 'Project updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update project: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->successResponse(null, 'Project deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete project: '.$e->getMessage(), 500);
        }
    }

    public function assignMember(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $this->service->assignMember($id, $request->user_id);

            return $this->successResponse(null, 'Member assigned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to assign member: '.$e->getMessage(), 500);
        }
    }

    public function removeMember(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $this->service->removeMember($id, $request->user_id);

            return $this->successResponse(null, 'Member removed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove member: '.$e->getMessage(), 500);
        }
    }

    public function getByUser(int $userId): JsonResponse
    {
        try {
            $projects = $this->service->getByUser($userId);

            return $this->successResponse(ProjectResource::collection($projects), 'Projects fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch projects: '.$e->getMessage(), 500);
        }
    }

    public function filter(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status', 'visibility', 'user_id', 'created_by',
                'from_date', 'to_date', 'sort_by', 'sort_dir',
            ]);
            $projects = $this->service->filter($filters);

            return $this->successResponse(ProjectResource::collection($projects), 'Projects filtered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to filter projects: '.$e->getMessage(), 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate(['q' => 'required|string|min:1']);
            $projects = $this->service->search($request->q);

            return $this->successResponse(ProjectResource::collection($projects), 'Projects searched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to search projects: '.$e->getMessage(), 500);
        }
    }
}
