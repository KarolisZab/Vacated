<?php

namespace App\Controller\API;

use App\Service\ReservedDayManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ReservedDayController extends AbstractController
{
    public function __construct(
        private ReservedDayManager $reservedDayManager,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/api/reserved-day', name: 'get_reserveddays', methods: ['GET'])]
    public function getReservedDays(Request $request)
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        if (null === $startDate || null === $endDate) {
            return new JsonResponse(
                'Invalid request. Start date and end date are required.',
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $reservedDays = $this->reservedDayManager->getReservedDays($startDate, $endDate);

        return new JsonResponse($this->serializer->serialize($reservedDays, 'json'), JsonResponse::HTTP_OK, [], true);
    }
}
