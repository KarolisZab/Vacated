<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    public function __construct(
        #[Assert\NotBlank(groups: ['update'])]
        public readonly string $firstName,
        #[Assert\NotBlank(groups: ['update'])]
        public readonly string $lastName,
        #[Assert\NotBlank(groups: ['update'])]
        public readonly string $phoneNumber,
    ) {
    }
}
