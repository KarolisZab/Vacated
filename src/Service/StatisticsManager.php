<?php

namespace App\Service;

use App\Entity\Vacation;
use App\Trait\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;

class StatisticsManager
{
    use LoggerTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserManager $userManager
    ) {
    }

    public function getMonthlyVacationStatisticsForYear(?string $dateFrom = null, ?string $dateTo = null): array
    {
        if ($dateFrom === null) {
            $startDate = new \DateTimeImmutable(date('Y-01-01'));
        } else {
            $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom);
        }
        if ($dateTo === null) {
            $endDate = new \DateTimeImmutable(date('Y-12-31'));
        } else {
            $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', $dateTo);
        }

        /** @var \App\Repository\VacationRepository $vacationRepository */
        $vacationRepository = $this->entityManager->getRepository(Vacation::class);

        $confirmedVacations = $vacationRepository->getConfirmedVacationsForPeriod($startDate, $endDate);

        $statisticsBucket = [];

        $currentDay = $startDate;
        while ($currentDay <= $endDate) {
            $yearMonth = $currentDay->format('Y-m');

            if (!isset($statisticsBucket[$yearMonth])) {
                $statisticsBucket[$yearMonth] = 0;
            }

            $currentDay = $currentDay->modify('+1 day');
        }

        foreach ($confirmedVacations as $vacation) {
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
                $yearMonth = $date->format('Y-m');
                $statisticsBucket[$yearMonth]++;
            }
        }

        return $statisticsBucket;
    }

    public function getVacationPercentage(): array
    {
        $employeeCount = $this->userManager->getUsersCount();
        $totalAvailableDays = $employeeCount * 20;

        $totalUsedDays = $this->getTotalUsedVacationDays();

        $remainingAvailableDays = $totalAvailableDays - $totalUsedDays;

        return [
            "Used vacation days" => $totalUsedDays,
            "Remaining vacation days" => $remainingAvailableDays,
        ];
    }

    private function getTotalUsedVacationDays(): int
    {
        $users = $this->userManager->getAllUsers();

        $totalUsedDays = 0;
        foreach ($users as $user) {
            $totalUsedDays += (20 - $user->getAvailableDays());
        }

        return $totalUsedDays;
    }
}
