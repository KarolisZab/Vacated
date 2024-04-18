<?php

namespace App\DTO;

class TagDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $colorCode = null
    ) {
    }
}
