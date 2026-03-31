<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\LabelController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth.jwt')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth.jwt')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('users', UserController::class)->parameters(['users' => 'user']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('labels', LabelController::class);
    Route::apiResource('tags', TagController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('comments', CommentController::class);
    Route::apiResource('attachments', AttachmentController::class);

    Route::get('projects/filter', [ProjectController::class, 'filter']);
    Route::get('projects/search', [ProjectController::class, 'search']);

    Route::get('tasks/filter', [TaskController::class, 'filter']);
    Route::get('tasks/search', [TaskController::class, 'search']);

    Route::prefix('users/{user}')->group(function () {
        Route::post('assign-role', [UserController::class, 'assignRole']);
        Route::delete('remove-role', [UserController::class, 'removeRole']);
        Route::post('assign-project', [UserController::class, 'assignProject']);
        Route::delete('remove-project', [UserController::class, 'removeProject']);
    });

    Route::prefix('projects/{project}')->group(function () {
        Route::post('assign-member', [ProjectController::class, 'assignMember']);
        Route::delete('remove-member', [ProjectController::class, 'removeMember']);
    });

    Route::get('projects-by-user/{userId}', [ProjectController::class, 'getByUser']);

    Route::prefix('tasks/{task}')->group(function () {
        Route::post('assign-user', [TaskController::class, 'assignUser']);
        Route::delete('remove-user', [TaskController::class, 'removeUser']);
        Route::post('attach-tag', [TaskController::class, 'attachTag']);
        Route::delete('detach-tag', [TaskController::class, 'detachTag']);
    });

    Route::get('tasks-by-project/{projectId}', [TaskController::class, 'getByProject']);
    Route::get('tasks-by-user/{userId}', [TaskController::class, 'getByUser']);
    Route::get('comments-by-task/{taskId}', [CommentController::class, 'getByTask']);
    Route::get('attachments-by-task/{taskId}', [AttachmentController::class, 'getByTask']);
});
