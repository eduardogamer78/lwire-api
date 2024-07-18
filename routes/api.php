<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SyncPermissionController;


/** Auth Login */
Route::get('/me', [AuthController::class, 'me'])
    ->middleware('auth:sanctum')->name('auth.me');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum')->name('auth.logout');
Route::post('/auth', [AuthController::class, 'auth'])->name('auth.login');


Route::middleware('auth:sanctum', 'acl')->group(function () {

    /** Create Permissions */
    Route::apiResource('/permissions', PermissionController::class);

    /** Cadastrar Permissões de um Usuário */
    Route::get('/users/{user}/permissions', [SyncPermissionController::class, 'getPermission'])->name('users.permissions');
    Route::post('/users/{user}/permissions-sync', [SyncPermissionController::class, 'permissionSync'])->name('users.permissions.sync');

    /** Users Auth */
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

Route::get('/', function () {
    return response()->json(['message' => 'Hello World!']);
});
