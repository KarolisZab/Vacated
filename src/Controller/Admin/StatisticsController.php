<?php

namespace App\Controller\Admin;

use App\Service\StatisticsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    public function __construct(
        private StatisticsManager $statisticsManager
    ) {
    }

    #[Route('/api/admin/monthly-vacation-statistics', name: 'monthly_vacation_statistics', methods: ['GET'])]
    public function getMonthlyVacationStatistics(Request $request)
    {
        try {
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');

            $statistics = $this->statisticsManager->getMonthlyVacationStatisticsForYear($startDate, $endDate);

            return new JsonResponse($statistics, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/admin/vacation-percentage', name: 'vacation_percentage', methods: ['GET'])]
    public function getVacationPercentage(Request $request)
    {
        try {
            $percentage = $this->statisticsManager->getVacationPercentage();

            return new JsonResponse($percentage, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
