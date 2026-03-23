<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $service
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $roles = $this->service->all(['*'], ['users']);

        return RoleResource::collection($roles);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->service->create($request->validated());

        return (new RoleResource($role))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->service->findOrFail($id, ['*'], ['users']);

        return (new RoleResource($role))->response();
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());

        return (new RoleResource($this->service->findOrFail($id)))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Role deleted successfully']);
    }
}
