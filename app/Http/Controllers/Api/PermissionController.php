<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\DTO\Permissions\EditPermissionDTO;
use App\Http\Resources\PermissionResource;
use App\Repositories\PermissionRepository;
use App\DTO\Permissions\CreatePermissionDTO;
use App\Http\Requests\UpdatePermissionRequest;

class PermissionController extends Controller
{
    public function __construct(private PermissionRepository $permissionRepository)
    {
    }

    /** Display a listing of the resource. */
    public function index(Request $request)
    {
        $permissions = $this->permissionRepository->getPaginate(
            totalPerPage: $request->total_per_page ?? 15,
            page: $request->page ?? 1,
            filter: $request->get('filter', '')
        );

        return PermissionResource::collection($permissions);
    }

    /** Store a newly created resource in storage. */
    public function store(PermissionRequest $request)
    {
        $permissions = $this->permissionRepository
            ->createNew(
                new CreatePermissionDTO(
                    ...$request->validated()
                )
            );

        return new PermissionResource($permissions);
    }

    /** Display the specified resource. */
    public function show(string $id)
    {
        if ( ! $permissions = $this->permissionRepository->findById($id)) {
            return response()->json(['message' => 'Permission not found'], Response::HTTP_NOT_FOUND);
        }

        return new PermissionResource($permissions);
    }

    /** Update the specified resource in storage. */
    public function update(UpdatePermissionRequest $request, string $id)
    {
        $response = $this->permissionRepository->update(new EditPermissionDTO(...[$id, ...$request->validated()]));
        if( ! $response) {
            return response()->json(['message' => 'Permission not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['message' => 'Permission updated with success']);
    }

    /** Remove the specified resource from storage. */
    public function destroy(string $id)
    {
        if( ! $this->permissionRepository->delete($id)) {
            return response()->json(['message' => 'Permission not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
