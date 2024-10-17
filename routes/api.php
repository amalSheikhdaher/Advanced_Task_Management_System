<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::middleware('auth:api')->group(function () {
    Route::get('tasks', [TaskController::class, 'index']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::get('tasks/{task}', [TaskController::class, 'show']);
    Route::put('tasks/{tasks}', [TaskController::class, 'update']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);

    // Add comments to tasks
    Route::post('tasks/{id}/comments', [TaskController::class, 'addComment']);

    // Add attachments to tasks
    Route::post('tasks/{id}/attachments', [TaskController::class, 'addAttachment']);

    // // Update task status
    Route::put('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::post('/tasks/{id}/assign', [TaskController::class, 'assignTask']);
    Route::put('/tasks/{id}/reassign', [TaskController::class, 'reassignTask']);

    Route::get('/task', [TaskController::class, 'trashed']); // Route to get trashed (deleted) tasks
    Route::post('/tasks/{task}/restore',[TaskController::class, 'restoreTask']); // Route to restore a previously deleted task
    Route::delete('/tasks/force-delete/{task}', [TaskController::class, 'forceDelete']);
});

Route::apiResource('/users', UserController::class)
    ->middleware('auth:api');
    Route::controller(UserController::class)->group(function () {
        Route::get('/user','trashed'); // Route to get trashed (deleted) users
        Route::post('/users/{user}/restore','restoreUser'); // Route to restore a previously deleted user
        Route::delete('/users/force-delete/{user}', 'forceDelete'); // Route to force delete a user (permanently)
    })->middleware('auth:api');


Route::get('/reports/daily-tasks', [ReportController::class, 'generateReport'])->middleware('auth:api');
