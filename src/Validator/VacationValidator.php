<?php

namespace App\Validator;

use App\Entity\Vacation;
use App\Repository\ReservedDayRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class VacationValidator extends ConstraintValidator
{
    private ReservedDayRepository $reservedDayRepository;

    public function __construct(ReservedDayRepository $reservedDayRepository)
    {
        $this->reservedDayRepository = $reservedDayRepository;
    }

    /**
     * @param Vacation $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof VacationConstraint) {
            throw new UnexpectedTypeException($constraint, VacationConstraint::class);
        }

        if (!$value instanceof Vacation) {
            throw new UnexpectedTypeException($value, Vacation::class);
        }

        $reservedDays = $this->reservedDayRepository->findReservedDaysInPeriod(
            $value->getDateFrom(),
            $value->getDateTo()
        );

        if (count($reservedDays) === 0) {
            return;
        }

        foreach ($reservedDays as $reservedDay) {
            $reservedDayTags = $reservedDay->getTags();
            if (count($reservedDayTags) === 0) {
                $this->context
                    ->buildViolation($constraint->messageNoTags)
                    ->addViolation();
                return;
            }

            $userTags = $value->getRequestedBy()->getTags();
            foreach ($userTags as $userTag) {
                foreach ($reservedDayTags as $reservedDayTag) {
                    if ($userTag->getName() === $reservedDayTag->getName()) {
                        $this->context
                            ->buildViolation($constraint->messageTagConflict)
                            ->addViolation();
                        return;
                    }
                }
            }
        }
    }
}
