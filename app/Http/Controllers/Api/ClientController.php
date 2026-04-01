<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        try {
            $clients = Client::all();

            return $this->successResponse(ClientResource::collection($clients), 'Clients fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch clients: '.$e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:clients,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
            ]);

            $client = Client::create($validated);

            return $this->successResponse(new ClientResource($client), 'Client created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create client: '.$e->getMessage(), 500);
        }
    }

    public function show(Client $client): JsonResponse
    {
        try {
            return $this->successResponse(new ClientResource($client), 'Client fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch client: '.$e->getMessage(), 500);
        }
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:clients,email,'.$client->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
            ]);

            $client->update($validated);

            return $this->successResponse(new ClientResource($client), 'Client updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update client: '.$e->getMessage(), 500);
        }
    }

    public function destroy(Client $client): JsonResponse
    {
        try {
            $client->delete();

            return $this->successResponse(null, 'Client deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete client: '.$e->getMessage(), 500);
        }
    }
}
