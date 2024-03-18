<?php

namespace App\Controller\Admin;

use App\DTO\ReservedDayDTO;
use App\Service\ReservedDayManager;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ReservedDayController extends AbstractController
{
    public function __construct(
        private UserManager $userManager,
        private ReservedDayManager $reservedDayManager,
        private SerializerInterface $serializer,
        private Security $security
    ) {
    }

    #[Route('/api/admin/reserved-day', name: 'create_reserveddays', methods: ['POST'])]
    public function reserveDays(Request $request, #[MapRequestPayload()] ReservedDayDTO $reservedDayDTO)
    {
        try {
            $currentUser = $this->security->getUser();

            $reservedDayDTO->reservedBy = $this->userManager->getUserByEmail($currentUser->getUserIdentifier());

            $reservedDays = $this->reservedDayManager->reserveDays($reservedDayDTO);

            return new JsonResponse(
                $this->serializer->serialize($reservedDays, 'json'),
                JsonResponse::HTTP_CREATED,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }

    #[Route('/api/admin/reserved-day/{id}', name: 'update_reserveddays', methods: ['PATCH'])]
    public function updateReservedDays(
        Request $request,
        string $id,
        #[MapRequestPayload()] ReservedDayDTO $reservedDayDTO
    ) {
        try {
            $currentUser = $this->security->getUser();

            $reservedDayDTO->reservedBy = $this->userManager->getUserByEmail($currentUser->getUserIdentifier());

            $reservedDay = $this->reservedDayManager->updateReservedDays($id, $reservedDayDTO);

            if ($reservedDay === null) {
                return new JsonResponse('Vacation request not found', JsonResponse::HTTP_NOT_FOUND);
            };

            return new JsonResponse(
                $this->serializer->serialize($reservedDay, 'json'),
                JsonResponse::HTTP_OK,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }

    #[Route('/api/admin/reserved-day/{id}', name: 'delete_reserveddays', methods: ['DELETE'])]
    public function deleteReservedDays(string $id)
    {
        try {
            $reservedDay = $this->reservedDayManager->deleteReservedDays($id);

            if ($reservedDay === false) {
                return new JsonResponse('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse(
                $this->serializer->serialize($reservedDay, 'json'),
                JsonResponse::HTTP_OK,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }
}
