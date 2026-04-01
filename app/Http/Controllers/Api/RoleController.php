<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RoleService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $roles = $this->service->paginate($perPage, ['*'], ['users']);

            return $this->paginatedResponse($roles, 'Roles fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch roles: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->service->create($request->validated());

            return $this->successResponse(new RoleResource($role), 'Role created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create role: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->service->findOrFail($id, ['*'], ['users']);

            return $this->successResponse(new RoleResource($role), 'Role fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch role: '.$e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->update($id, $request->validated());
            $role = $this->service->findOrFail($id);

            return $this->successResponse(new RoleResource($role), 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update role: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->successResponse(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete role: '.$e->getMessage(), 500);
        }
    }
}
