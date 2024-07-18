<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Resources\PermissionResource;
use App\Http\Requests\SyncPermissionOfUser;

class SyncPermissionController extends Controller
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function permissionSync(string $id, SyncPermissionOfUser $request)
    {
        $response = $this->userRepository->syncPermissions($id, $request->permissions);
        if ( ! $response) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['message' => 'ok'], Response::HTTP_OK);
    }

    public function getPermission(string $id)
    {
        if ( ! $this->userRepository->findById($id)) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $permissions = $this->userRepository->getPermissionsById($id);

        return PermissionResource::collection($permissions);
    }
}
