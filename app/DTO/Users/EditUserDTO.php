<?php

declare(strict_types=1);

namespace App\DTO\Users;

final readonly class EditUserDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $password = null,
    ) {
    }
}
