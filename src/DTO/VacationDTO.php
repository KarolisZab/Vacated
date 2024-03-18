<?php

namespace App\DTO;

use App\Entity\User;

class VacationDTO
{
    public function __construct(
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
        public readonly ?string $note = '',
        public ?User $reviewedBy = null,
        public readonly ?string $rejectionNote = '',
    ) {
    }
}
