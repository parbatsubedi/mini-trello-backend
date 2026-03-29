<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $relations = ['department', 'roles'];
        $users = $this->service->paginate($perPage, ['*'], $relations);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->service->findOrFail($id, ['*'], ['department', 'roles', 'projects', 'createdTasks', 'assignedTasks']);

        return (new UserResource($user))->response();
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['password']) && ! $data['password']) {
            unset($data['password']);
        }

        $this->service->update($id, $data);

        return (new UserResource($this->service->findOrFail($id)))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function assignRole(Request $request, int $id): JsonResponse
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);
        $this->service->assignRole($id, $request->role_id);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    public function removeRole(Request $request, int $id): JsonResponse
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);
        $this->service->removeRole($id, $request->role_id);

        return response()->json(['message' => 'Role removed successfully']);
    }

    public function assignProject(Request $request, int $id): JsonResponse
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);
        $this->service->assignProject($id, $request->project_id);

        return response()->json(['message' => 'Project assigned successfully']);
    }

    public function removeProject(Request $request, int $id): JsonResponse
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);
        $this->service->removeProject($id, $request->project_id);

        return response()->json(['message' => 'Project removed successfully']);
    }
}
