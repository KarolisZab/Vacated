<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class VacationConstraint extends Constraint
{
    public string $messageNoTags = 'Vacation cannot be requested on reserved days.';
    public string $messageTagConflict = 'Vacation cannot be requested if user tag conflicts with reserved days tags.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return VacationValidator::class;
    }
}
