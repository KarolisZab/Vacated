<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    public function __construct(
        #[Assert\NotBlank(groups: ['create'])]
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: 'Email must contain at least {{ limit }} characters',
            maxMessage: 'Email must not exceed {{ limit }} characters'
        )]
        public readonly ?string $email,
        #[Assert\NotBlank(groups: ['create', 'update'])]
        public readonly ?string $password = null,
        #[Assert\NotBlank(groups: ['update'])]
        public readonly ?string $firstName,
        #[Assert\NotBlank(groups: ['update'])]
        public readonly ?string $lastName,
        #[Assert\NotBlank(groups: ['update'])]
        public readonly ?string $phoneNumber,
    ) {
    }
}
