<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Permission;
use App\DTO\Permissions\EditPermissionDTO;
use App\DTO\Permissions\CreatePermissionDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionRepository
{
    public function __construct(protected Permission $permission)
    {
    }

    public function getPaginate($totalPerPage = 15, $page = 1, string $filter = ''): LengthAwarePaginator
    {
        return $this->permission->where(function ($query) use ($filter) {
            if ($filter !== '') {
                $query->where('name', 'LIKE', "%{$filter}%");
            }
        })
            ->paginate($totalPerPage, ['*'], 'page', $page);
    }

    public function createNew(CreatePermissionDTO $dto): Permission
    {
        return $this->permission->create((array) $dto);
    }

    public function findById(string $id): ?Permission
    {
        return $this->permission->find($id);
    }

    public function findByEmail(string $email): ?Permission
    {
        return $this->permission->where('email', $email)->first();
    }

    public function update(EditPermissionDTO $dto): bool
    {
        if ( ! $permission = $this->findById($dto->id)) {
            return false;
        }

        return $permission->update((array) $dto);
    }

    public function delete(string $id): bool
    {
        if ( ! $permission = $this->findById($id)) {
            return false;
        }

        return $permission->delete();
    }
}
