<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ReservedDayDTO
{
    public function __construct(
        #[Assert\NotBlank(groups: ['create', 'update'])]
        public readonly ?string $reservedFrom,
        #[Assert\NotBlank(groups: ['create', 'update'])]
        public readonly ?string $reservedTo,
        #[Assert\Length(max: 255)]
        public readonly ?string $reservedNote = '',
    ) {
    }
}
