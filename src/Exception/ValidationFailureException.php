<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationFailureException extends \Exception
{
    public static function throwException(ConstraintViolationListInterface $violations): void
    {
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new self(json_encode($errors), 400);
        }
    }
}
