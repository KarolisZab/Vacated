<?php

namespace App\Service;

use App\DTO\ReservedDayDTO;
use App\Entity\ReservedDay;
use App\Exception\ValidationFailureException;
use App\Trait\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservedDayManager
{
    use LoggerTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    public function reserveDays(ReservedDayDTO $reservedDayDTO): ReservedDay
    {
        try {
            $now = new \DateTimeImmutable();
            $from = \DateTimeImmutable::createFromFormat('Y-m-d', $reservedDayDTO->dateFrom);
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $reservedDayDTO->dateTo);

            if ($from < $now) {
                throw new \InvalidArgumentException("Date cannot be in the past.", 400);
            }

            if ($to < $from) {
                throw new \InvalidArgumentException("Raserved day cannot end before it starts.", 400);
            }

            $reservedDay = new ReservedDay();
            $reservedDay
                ->setReservedBy($reservedDayDTO->reservedBy)
                ->setDateFrom($from)
                ->setDateTo($to)
                ->setNote($reservedDayDTO->note);

            $errors = $this->validator->validate($reservedDay, null, ['create']);
            ValidationFailureException::throwException($errors);

            $this->entityManager->persist($reservedDay);
            $this->entityManager->flush();

            return $reservedDay;
        } catch (ORMException $e) {
            $this->logger->critical(
                "Exception occured while reserving calendar days." . $e->getMessage()
            );
            throw $e;
        }
    }

    public function updateReservedDays(string $id, ReservedDayDTO $reservedDayDTO): ?ReservedDay
    {
        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);
        $reservedDay = $reservedDayRepository->find($id);

        if ($reservedDay === null) {
            return null;
        }

        $now = new \DateTimeImmutable();

        $from = $reservedDay->getDateFrom();
        if ($reservedDayDTO->dateFrom !== null) {
            $from = \DateTimeImmutable::createFromFormat('Y-m-d', $reservedDayDTO->dateFrom);
        }

        $to = $reservedDay->getDateTo();
        if ($reservedDayDTO->dateTo !== null) {
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $reservedDayDTO->dateTo);
        }

        if ($from < $now) {
            throw new \InvalidArgumentException("Date cannot be in the past.", 400);
        }

        if ($to < $from) {
            throw new \InvalidArgumentException("Raserved day cannot end before it starts.", 400);
        }

        $reservedDay
            ->setDateFrom($from)
            ->setDateTo($to)
            ->setNote($reservedDayDTO->note);

        $errors = $this->validator->validate($reservedDay, null, ['update']);
        ValidationFailureException::throwException($errors);

        $this->entityManager->flush();

        $this->logger->info("Reserved days were updated by administrator {$reservedDayDTO->reservedBy->getEmail()}");

        return $reservedDay;
    }

    public function deleteReservedDays(string $id): bool
    {
        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);
        $reservedDay = $reservedDayRepository->find($id);

        if ($reservedDay === null) {
            return false;
        }

        $this->entityManager->remove($reservedDay);
        $this->entityManager->flush();

        $this->logger->info(
            "Reserved days from {$reservedDay->getDateFrom()->format('Y-m-d')} 
            to {$reservedDay->getDateTo()->format('Y-m-d')} has been deleted."
        );

        return true;
    }

    public function getReservedDay(string $id): ?ReservedDay
    {
        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);

        return $reservedDayRepository->find($id);

        // ID nezinosiu, reiketu pasidaryt find pagal datas
    }

    public function getReservedDays(string $dateFrom, string $dateTo): array
    {
        $from = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom);
        $to = \DateTimeImmutable::createFromFormat('Y-m-d', $dateTo);

        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);

        return $reservedDayRepository->findReservedDaysInPeriod($from, $to);
    }
}
