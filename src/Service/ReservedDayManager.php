<?php

namespace App\Service;

use App\DTO\ReservedDayDTO;
use App\Entity\User;
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

    public function reserveDays(User $user, ReservedDayDTO $reservedDayDTO): ReservedDay
    {
        try {
            $now = new \DateTimeImmutable();
            $from = \DateTimeImmutable::createFromFormat('Y-m-d', $reservedDayDTO->reservedFrom);
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $reservedDayDTO->reservedTo);

            if ($from < $now || $to < $now) {
                throw new \InvalidArgumentException("Date cannot be in the past.", 400);
            }

            $reservedDay = new ReservedDay();
            $reservedDay
                ->setReservedBy($user)
                ->setReservedFrom($from)
                ->setReservedTo($to)
                ->setReservedNote($reservedDayDTO->reservedNote);

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

    public function updateReservedDays(string $id, User $user, ReservedDayDTO $reservedDayDTO): ReservedDay
    {
        if (!$user->hasRole('ROLE_ADMIN')) {
            throw new \Exception('Forbidden', 403);
        }

        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);
        /** @var ReservedDay $reservedDay */
        $reservedDay = $reservedDayRepository->find($id);

        if ($reservedDay === null) {
            return null;
        }

        $now = new \DateTimeImmutable();

        $from = $reservedDay->getReservedFrom();
        if ($reservedDayDTO->reservedFrom !== null) {
            $from = \DateTimeImmutable::createFromFormat('Y-m-d', $reservedDayDTO->reservedFrom);
        }

        $to = $reservedDay->getReservedTo();
        if ($reservedDayDTO->reservedTo !== null) {
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $reservedDayDTO->reservedTo);
        }

        if ($from < $now || $to < $now) {
            throw new \InvalidArgumentException("Date cannot be in the past.");
        }

        $reservedDay
            ->setReservedFrom($from)
            ->setReservedTo($to)
            ->setReservedNote($reservedDayDTO->reservedNote);

        $errors = $this->validator->validate($reservedDay, null, ['update']);
        ValidationFailureException::throwException($errors);

        $this->entityManager->flush();

        $this->logger->info("Reserved days were updated by administrator {$user->getEmail()}");

        return $reservedDay;
    }

    public function deleteReservedDays(string $id, User $user): bool
    {
        if (!$user->hasRole('ROLE_ADMIN')) {
            throw new \Exception('Forbidden', 403);
        }

        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);
        $reservedDay = $reservedDayRepository->find($id);

        if ($reservedDay === null) {
            return false;
        }

        $this->entityManager->remove($reservedDay);
        $this->entityManager->flush();

        $this->logger->info(
            "Reserved days from {$reservedDay->getReservedFrom()} to {$reservedDay->getReservedTo()} has been deleted."
        );

        return true;
    }

    public function getReservedDay(string $id): ?ReservedDay
    {
        /** @var \App\Repository\ReservedDayRepository $reservedDayRepository */
        $reservedDayRepository = $this->entityManager->getRepository(ReservedDay::class);

        return $reservedDayRepository->find($id);
    }
}
