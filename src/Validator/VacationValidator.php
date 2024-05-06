<?php

namespace App\Validator;

use App\Entity\ReservedDay;
use App\Entity\Vacation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class VacationValidator extends ConstraintValidator
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param Vacation $value
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);

        if (!$constraint instanceof VacationConstraint) {
            throw new UnexpectedTypeException($constraint, VacationConstraint::class);
        }

        if (!$value instanceof Vacation) {
            throw new UnexpectedTypeException($value, Vacation::class);
        }

        $reservedDays = $reservedDayRepository->findReservedDaysInPeriod(
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
