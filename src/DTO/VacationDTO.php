<?php

namespace App\DTO;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class VacationDTO
{
    public function __construct(
        #[Assert\NotBlank(groups: ['create', 'update'])]
        public readonly ?string $dateFrom,
        #[Assert\NotBlank(groups: ['create', 'update'])]
        public readonly ?string $dateTo,
        #[Assert\Length(max: 255)]
        public readonly ?string $note = '',
        #[Assert\NotBlank(groups: ['confirm', 'reject'])]
        public readonly ?User $reviewedBy = null,
        #[Assert\NotBlank(groups: ['reject'])]
        #[Assert\Length(max: 255)]
        public readonly ?string $rejectionNote = '',
    ) {
    }
}
