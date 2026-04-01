<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DepartmentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $departments = $this->service->paginate($perPage, ['*'], ['users', 'projects']);

            return $this->paginatedResponse($departments, 'Departments fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch departments: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        try {
            $department = $this->service->create($request->validated());

            return $this->successResponse(new DepartmentResource($department), 'Department created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create department: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $department = $this->service->findOrFail($id, ['*'], ['users', 'projects']);

            return $this->successResponse(new DepartmentResource($department), 'Department fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch department: '.$e->getMessage(), 500);
        }
    }

    public function update(UpdateDepartmentRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->update($id, $request->validated());
            $department = $this->service->findOrFail($id);

            return $this->successResponse(new DepartmentResource($department), 'Department updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update department: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->successResponse(null, 'Department deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete department: '.$e->getMessage(), 500);
        }
    }
}
