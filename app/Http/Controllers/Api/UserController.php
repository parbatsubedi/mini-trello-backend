<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected UserService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $relations = ['department', 'roles'];
            $users = $this->service->paginate($perPage, ['*'], $relations);

            return $this->paginatedResponse($users, 'Users fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch users: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->service->create($request->validated());

            return $this->successResponse(new UserResource($user), 'User created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create user: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->service->findOrFail($id, ['*'], ['department', 'roles', 'projects', 'createdTasks', 'assignedTasks']);

            return $this->successResponse(new UserResource($user), 'User fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch user: '.$e->getMessage(), 500);
        }
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            if (isset($data['password']) && ! $data['password']) {
                unset($data['password']);
            }

            $this->service->update($id, $data);
            $user = $this->service->findOrFail($id);

            return $this->successResponse(new UserResource($user), 'User updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update user: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->successResponse(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete user: '.$e->getMessage(), 500);
        }
    }

    public function assignRole(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['role_id' => 'required|exists:roles,id']);
            $this->service->assignRole($id, $request->role_id);

            return $this->successResponse(null, 'Role assigned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to assign role: '.$e->getMessage(), 500);
        }
    }

    public function removeRole(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['role_id' => 'required|exists:roles,id']);
            $this->service->removeRole($id, $request->role_id);

            return $this->successResponse(null, 'Role removed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove role: '.$e->getMessage(), 500);
        }
    }

    public function assignProject(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['project_id' => 'required|exists:projects,id']);
            $this->service->assignProject($id, $request->project_id);

            return $this->successResponse(null, 'Project assigned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to assign project: '.$e->getMessage(), 500);
        }
    }

    public function removeProject(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['project_id' => 'required|exists:projects,id']);
            $this->service->removeProject($id, $request->project_id);

            return $this->successResponse(null, 'Project removed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove project: '.$e->getMessage(), 500);
        }
    }
}
