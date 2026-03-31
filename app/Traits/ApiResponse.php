<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param  mixed  $data
     */
    public function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param  mixed  $data
     */
    public function errorResponse(string $message, int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a paginated JSON response.
     *
     * @param  mixed  $paginator
     */
    public function paginatedResponse($paginator, string $message = 'Success', int $code = 200): JsonResponse
    {
        $paginatorInstance = $paginator;
        if ($paginator instanceof ResourceCollection) {
            $paginatorInstance = $paginator->resource;
            $data = $paginator->response()->getData(true)['data'];
        } else {
            $data = $paginator->items();
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'links' => [
                'first' => $paginatorInstance->url(1),
                'last' => $paginatorInstance->url($paginatorInstance->lastPage()),
                'prev' => $paginatorInstance->previousPageUrl(),
                'next' => $paginatorInstance->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginatorInstance->currentPage(),
                'from' => $paginatorInstance->firstItem(),
                'last_page' => $paginatorInstance->lastPage(),
                'path' => $paginatorInstance->path(),
                'per_page' => $paginatorInstance->perPage(),
                'to' => $paginatorInstance->lastItem(),
                'total' => $paginatorInstance->total(),
            ],
        ], $code);
    }
}
