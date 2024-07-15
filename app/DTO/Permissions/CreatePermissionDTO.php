<?php

namespace App\DTO\Permissions;

final readonly class CreatePermissionDTO
{
    public function __construct(
        public string $name,
        public string $description = '',
    ) {}
}
