<?php

namespace App\Controller\Admin;

use App\Service\StatisticsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class StatisticsController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private Security $security,
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
}
