<?php


use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketAssignmentController;
use App\Http\Controllers\StatsController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::post('tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
    Route::get('/tickets/search', [TicketController::class, 'search']);

    Route::apiResource('tickets.comments', CommentController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->shallow();

    Route::post('tickets/{ticket}/assign', [TicketAssignmentController::class, 'assign']);
    Route::post('tickets/{ticket}/auto-assign', [TicketAssignmentController::class, 'autoAssign']);
    Route::post('tickets/{ticket}/unassign', [TicketAssignmentController::class, 'unassign']);

    Route::get('/stats/dashboard', [StatsController::class, 'dashboard']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/stats/agents', [StatsController::class, 'agentPerformance']);
    });
});
