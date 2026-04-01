<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $relations = ['creator', 'department', 'members', 'client', 'labels','tasks'];
        $projects = $this->service->paginate($perPage, ['*'], $relations);

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->service->create($request->validated());

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $project = $this->service->findOrFail($id, ['*'], ['creator', 'department', 'members', 'tasks', 'client', 'labels','tasks.assignee', 'tasks.tags', 'tasks.attachments', 'tasks.collaborators', 'tasks.comments']);

        return (new ProjectResource($project))->response();
    }

    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());

        return (new ProjectResource($this->service->findOrFail($id)))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Project deleted successfully']);
    }

    public function assignMember(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $this->service->assignMember($id, $request->user_id);

        return response()->json(['message' => 'Member assigned successfully']);
    }

    public function removeMember(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $this->service->removeMember($id, $request->user_id);

        return response()->json(['message' => 'Member removed successfully']);
    }

    public function getByUser(int $userId): AnonymousResourceCollection
    {
        $projects = $this->service->getByUser($userId);

        return ProjectResource::collection($projects);
    }

    public function filter(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'status', 'visibility', 'user_id', 'created_by',
            'from_date', 'to_date', 'sort_by', 'sort_dir',
        ]);
        $projects = $this->service->filter($filters);

        return ProjectResource::collection($projects);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate(['q' => 'required|string|min:1']);
        $projects = $this->service->search($request->q);

        return ProjectResource::collection($projects);
    }
}
