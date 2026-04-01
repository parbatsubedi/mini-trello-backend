<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TaskService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $relations = ['project', 'creator', 'assignee', 'tags', 'assignedUsers', 'labels'];
            $tasks = $this->service->paginate($perPage, ['*'], $relations);

            return $this->paginatedResponse($tasks, 'Tasks fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch tasks: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = $this->service->create($request->validated());

            return $this->successResponse(new TaskResource($task), 'Task created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create task: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $relations = ['project', 'creator', 'assignee', 'parent', 'subtasks', 'tags', 'assignedUsers', 'comments', 'attachments', 'labels', 'collaborators'];
            $task = $this->service->findOrFail($id, ['*'], $relations);

            return $this->successResponse(new TaskResource($task), 'Task fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch task: '.$e->getMessage(), 500);
        }
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->update($id, $request->validated());
            $task = $this->service->findOrFail($id);

            return $this->successResponse(new TaskResource($task), 'Task updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update task: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->successResponse(null, 'Task deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete task: '.$e->getMessage(), 500);
        }
    }

    public function assignUser(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['user_ids' => 'required|array', 'user_ids.*' => 'exists:users,id']);
            $this->service->assignUsers($id, $request->user_ids);

            return $this->successResponse(null, 'Users assigned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to assign users: '.$e->getMessage(), 500);
        }
    }

    public function assignCollaborators(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['user_ids' => 'required|array', 'user_ids.*' => 'exists:users,id']);
            $this->service->assignCollaborators($id, $request->user_ids);

            return $this->successResponse(null, 'Collaborators assigned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to assign collaborators: '.$e->getMessage(), 500);
        }
    }

    public function removeUser(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $this->service->removeUser($id, $request->user_id);

            return $this->successResponse(null, 'User removed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove user: '.$e->getMessage(), 500);
        }
    }

    public function removeCollaborator(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $this->service->removeCollaborator($id, $request->user_id);

            return $this->successResponse(null, 'Collaborator removed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove collaborator: '.$e->getMessage(), 500);
        }
    }

    public function attachTag(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['tag_id' => 'required|exists:tags,id']);
            $this->service->attachTag($id, $request->tag_id);

            return $this->successResponse(null, 'Tag attached successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to attach tag: '.$e->getMessage(), 500);
        }
    }

    public function detachTag(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['tag_id' => 'required|exists:tags,id']);
            $this->service->detachTag($id, $request->tag_id);

            return $this->successResponse(null, 'Tag detached successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to detach tag: '.$e->getMessage(), 500);
        }
    }

    public function getByProject(int $projectId): JsonResponse
    {
        try {
            $tasks = $this->service->getByProject($projectId);

            return $this->successResponse(TaskResource::collection($tasks), 'Tasks fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch tasks: '.$e->getMessage(), 500);
        }
    }

    public function getByUser(int $userId): JsonResponse
    {
        try {
            $tasks = $this->service->getByUser($userId);

            return $this->successResponse(TaskResource::collection($tasks), 'Tasks fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch tasks: '.$e->getMessage(), 500);
        }
    }

    public function filter(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status', 'priority', 'project_id', 'user_id', 'assigned_to',
                'tag_id', 'from_date', 'to_date', 'overdue', 'created_by',
                'sort_by', 'sort_dir',
            ]);
            $tasks = $this->service->filter($filters);

            return $this->successResponse(TaskResource::collection($tasks), 'Tasks filtered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to filter tasks: '.$e->getMessage(), 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate(['q' => 'required|string|min:1']);
            $tasks = $this->service->search($request->q);

            return $this->successResponse(TaskResource::collection($tasks), 'Tasks searched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to search tasks: '.$e->getMessage(), 500);
        }
    }
}
