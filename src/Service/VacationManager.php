<?php

namespace App\Service;

use App\DTO\VacationDTO;
use App\Entity\ReservedDay;
use App\Entity\User;
use App\Entity\Vacation;
use App\Exception\ValidationFailureException;
use App\Trait\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VacationManager
{
    use LoggerTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    public function requestVacation(User $user, VacationDTO $vacationDTO): Vacation
    {
        try {
            $now = new \DateTimeImmutable();
            $from = \DateTimeImmutable::createFromFormat('Y-m-d', $vacationDTO->dateFrom);
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $vacationDTO->dateTo);

            if ($from < $now || $to < $now) {
                throw new \InvalidArgumentException("Date cannot be in the past.", 400);
            }

            /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
            $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);
            /** @var ReservedDay $reservedDays */
            $reservedDays = $reservedDayRepository->findReservedDaysInPeriod($from, $to);

            if (!empty($reservedDays)) {
                throw new \InvalidArgumentException("Vacation cannot be requested on reserved days.", 400);
            }

            $vacation = new Vacation();
            $vacation->setRequestedBy($user)
                ->setDateFrom($from)
                ->setDateTo($to)
                ->setNote($vacationDTO->note);

            $errors = $this->validator->validate($vacation, null, ['create']);
            ValidationFailureException::throwException($errors);

            $this->entityManager->persist($vacation);
            $this->entityManager->flush();

            return $vacation;
        } catch (ORMException $e) {
            $this->logger->critical(
                "Exception occured while creating vacation reservation for {$user->getEmail()}: " . $e->getMessage()
            );
            throw $e;
        }
        // TODO: siusti laiska per maileri
    }

    public function updateRequestedVacation(string $id, User $user, VacationDTO $vacationDTO): ?Vacation
    {
        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);
        $vacation = $vacationRepository->find($id);

        if ($vacation === null) {
            return null;
        }

        if ($vacation->getRequestedBy() !== $user) {
            throw new \InvalidArgumentException("You are not authorized to update this vacation request.", 400);
        }

        $now = new \DateTimeImmutable();

        $from = $vacation->getDateFrom();
        if ($vacationDTO->dateFrom !== null) {
            $from = \DateTimeImmutable::createFromFormat('Y-m-d', $vacationDTO->dateFrom);
        }

        $to = $vacation->getDateTo();
        if ($vacationDTO->dateTo !== null) {
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $vacationDTO->dateTo);
        }

        if ($from < $now || $to < $now) {
            throw new \InvalidArgumentException("Date cannot be in the past.", 400);
        }

        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);
        /** @var ReservedDay $reservedDays */
        $reservedDays = $reservedDayRepository->findReservedDaysInPeriod($from, $to);

        if (!empty($reservedDays)) {
            throw new \InvalidArgumentException("Vacation cannot be requested on reserved days.", 400);
        }

        $vacation->setDateFrom($from)
            ->setDateTo($to)
            ->setNote($vacationDTO->note);

        $errors = $this->validator->validate($vacation, null, ['update']);
        ValidationFailureException::throwException($errors);

        $this->entityManager->flush();

        return $vacation;

        //TODO: siusti laiska per maileri adminams po updeito probably
    }

    public function rejectVacationRequest(string $id, VacationDTO $vacationDTO): ?Vacation
    {
        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);
        $vacation = $vacationRepository->find($id);

        if ($vacation === null) {
            return null;
        }

        $vacation->setReviewedAt(new \DateTimeImmutable())
            ->setReviewedBy($vacationDTO->reviewedBy)
            ->setRejectionNote($vacationDTO->rejectionNote)
            ->setRejected(true);

        $errors = $this->validator->validate($vacation, null, ['reject']);
        ValidationFailureException::throwException($errors);

        $this->entityManager->flush();

        return $vacation;

        //TODO: siust laiska tures
    }

    public function confirmVacationRequest(string $id, VacationDTO $vacationDTO): ?Vacation
    {
        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);
        $vacation = $vacationRepository->find($id);

        if ($vacation === null) {
            return null;
        }

        $vacation->setReviewedAt(new \DateTimeImmutable())
            ->setReviewedBy($vacationDTO->reviewedBy)
            ->setConfirmed(true);

        $errors = $this->validator->validate($vacation, null, ['confirm']);
        ValidationFailureException::throwException($errors);

        $this->entityManager->flush();

        return $vacation;

        //TODO: siust laiska tures
    }

    public function getVacationRequest(string $id): ?Vacation
    {
        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);

        return $vacationRepository->find($id);
    }

    public function getVacationsDaysForCalendar(string $dateFrom, string $dateTo, User $user): array
    {
        $vacationBucket = [];

        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom);
        $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', $dateTo);

        $currentDay = $startDate;

        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);

        $confirmed = $vacationRepository->getConfirmedVacationsForPeriod($startDate, $endDate);
        $requested = $vacationRepository->getRequestedVacationsForPeriodByUser($startDate, $endDate, $user->getId());

        $vacations = array_merge($confirmed, $requested);

        while ($currentDay <= $endDate) {
            $day = $currentDay->format('Y-m-d');
            $vacationBucket[$day] = [];
            $currentDay = $currentDay->modify('+1 day');
        }

        foreach ($vacations as $vacation) {
            $vacationStartDate = $vacation->getDateFrom();
            $vacationEndDate = $vacation->getDateTo();

            if ($vacationStartDate < $startDate) {
                $vacationStartDate = $startDate;
            }

            if ($vacationEndDate > $endDate) {
                $vacationEndDate = $endDate;
            }

            $interval = \DateInterval::createFromDateString('1 day');
            $period = new \DatePeriod($vacationStartDate, $interval, $vacationEndDate);

            foreach ($period as $date) {
                $vacationBucket[$date->format('Y-m-d')][] = $vacation;
            }
        }

        return $vacationBucket;
    }
}
