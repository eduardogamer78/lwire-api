<?php

declare(strict_types=1);

namespace App\DTO\Permissions;

final readonly class EditPermissionDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
    ) {
    }
}
