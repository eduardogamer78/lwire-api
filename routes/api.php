<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SyncPermissionController;

Route::post('/auth', [AuthController::class, 'auth'])->name('auth.login');

Route::withoutMiddleware('auth:sanctum', 'acl')->group(function () {

    /** Users Permissions */
    Route::apiResource('/permissions', PermissionController::class);

    /** Users Roles */
    Route::get('/users/{user}/permissions', [SyncPermissionController::class, 'getPermission'])->name('get.permissions');
    Route::post('/users/{user}/permissions-sync', [SyncPermissionController::class, 'permissionSync'])->name('permissions.sync');

    /** Users Auth */
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    /** Auth Login */
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::get('/', function () {
    return response()->json(['message' => 'Hello World!']);
});

