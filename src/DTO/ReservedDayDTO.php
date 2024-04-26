<?php

namespace App\DTO;

use App\Entity\User;

class ReservedDayDTO
{
    public function __construct(
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
        public ?User $reservedBy,
        public readonly ?string $note = '',
        public readonly ?array $tags = [],
    ) {
    }
}
