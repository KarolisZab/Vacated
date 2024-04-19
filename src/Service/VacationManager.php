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

            if ($from < $now) {
                throw new \InvalidArgumentException("Date cannot be in the past.", 400);
            }

            if ($to < $from) {
                throw new \InvalidArgumentException("Vacation cannot end before it starts.", 400);
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

        if ($from < $now) {
            throw new \InvalidArgumentException("Date cannot be in the past.", 400);
        }

        if ($to < $from) {
            throw new \InvalidArgumentException("Vacation cannot end before it starts.", 400);
        }

        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);
        $reservedDays = $reservedDayRepository->findReservedDaysInPeriod($from, $to);

        if (count($reservedDays) > 0) {
            throw new \InvalidArgumentException("Vacation cannot be requested on reserved days.", 400);
        }

        if ($vacation->isConfirmed() === true) {
            $vacation->setConfirmed(false);
            $this->logger->info('Confirmed vacation ' . $id . ' was updated and needs to be reviewed again.');
        }

        //TODO: issiuncia laiska adminui, kad confirmed vacation buvo paredaguotos

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

    public function getAllVacations(): array
    {
        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);

        return $vacationRepository->findAll();
    }

    public function getAllCurrentUserVacations(User $user): array
    {
        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);

        return $vacationRepository->findBy(['requestedBy' => $user]);
    }

    public function getVacations(int $limit = 10, int $offset = 0, /*?string $filter = null*/): array
    {

        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);

        return $vacationRepository->getVacations($limit, $offset, /*$filter*/);
    }

    // public function getVacationsCount(?string $filter = null): int
    // {
    //     /** @var \App\Repository\UserRepository $userRepository */
    //     $userRepository = $this->entityManager->getRepository(User::class);

    //     return $userRepository->countAllVacations($filter);
    // }

    /**
     * @return array<string, Vacation[]>
     */
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

        /** @var Vacation $vacation */
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

    public function getConfirmedVacationDaysInYear(): int
    {
        $currentYear = date("Y");

        $startDate = new \DateTimeImmutable("$currentYear-01-01");
        $endDate = new \DateTimeImmutable("$currentYear-12-31");

        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);

        $confirmedVacations = $vacationRepository->getConfirmedVacationsForPeriod($startDate, $endDate);

        $confirmedVacationDays = 0;

        foreach ($confirmedVacations as $vacation) {
            $vacationStartDate = $vacation->getDateFrom();
            $vacationEndDate = $vacation->getDateTo();

            $interval = $vacationStartDate->diff($vacationEndDate);
            $confirmedVacationDays += $interval->days + 1;
        }

        return $confirmedVacationDays;
    }

    public function getPendingVacationRequestCount(): int
    {
        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);

        return $vacationRepository->count(['isConfirmed' => false, 'isRejected' => false]);
    }
}
