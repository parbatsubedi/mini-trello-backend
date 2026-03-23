<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $relations = ['project', 'creator', 'assignee', 'tags', 'assignedUsers'];
        $tasks = $this->service->all(['*'], $relations);

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->service->create($request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $relations = ['project', 'creator', 'assignee', 'parent', 'subtasks', 'tags', 'assignedUsers', 'comments', 'attachments'];
        $task = $this->service->findOrFail($id, ['*'], $relations);

        return (new TaskResource($task))->response();
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());

        return (new TaskResource($this->service->findOrFail($id)))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function assignUser(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $this->service->assignUser($id, $request->user_id);

        return response()->json(['message' => 'User assigned successfully']);
    }

    public function removeUser(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $this->service->removeUser($id, $request->user_id);

        return response()->json(['message' => 'User removed successfully']);
    }

    public function attachTag(Request $request, int $id): JsonResponse
    {
        $request->validate(['tag_id' => 'required|exists:tags,id']);
        $this->service->attachTag($id, $request->tag_id);

        return response()->json(['message' => 'Tag attached successfully']);
    }

    public function detachTag(Request $request, int $id): JsonResponse
    {
        $request->validate(['tag_id' => 'required|exists:tags,id']);
        $this->service->detachTag($id, $request->tag_id);

        return response()->json(['message' => 'Tag detached successfully']);
    }

    public function getByProject(int $projectId): AnonymousResourceCollection
    {
        $tasks = $this->service->getByProject($projectId);

        return TaskResource::collection($tasks);
    }

    public function getByUser(int $userId): AnonymousResourceCollection
    {
        $tasks = $this->service->getByUser($userId);

        return TaskResource::collection($tasks);
    }

    public function filter(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'status', 'priority', 'project_id', 'user_id', 'assigned_to',
            'tag_id', 'from_date', 'to_date', 'overdue', 'created_by',
            'sort_by', 'sort_dir',
        ]);
        $tasks = $this->service->filter($filters);

        return TaskResource::collection($tasks);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate(['q' => 'required|string|min:1']);
        $tasks = $this->service->search($request->q);

        return TaskResource::collection($tasks);
    }
}
