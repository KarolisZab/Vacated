<?php

namespace App\DTO;

class UserDTO
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $phoneNumber,
        public ?string $password = null,
        public readonly ?array $tags = [],
    ) {
    }
}
