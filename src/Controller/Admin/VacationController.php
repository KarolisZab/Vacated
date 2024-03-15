<?php

namespace App\Controller\Admin;

use App\DTO\VacationDTO;
use App\Service\UserManager;
use App\Service\VacationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class VacationController extends AbstractController
{
    public function __construct(
        private UserManager $userManager,
        private VacationManager $vacationManager,
        private SerializerInterface $serializer,
        private Security $security
    ) {
    }

    #[Route('/api/admin/reject-vacation/{id}', name: 'reject_vacation', methods: ['PATCH'])]
    public function rejectVacationRequest(Request $request, string $id, #[MapRequestPayload()] VacationDTO $vacationDTO)
    {
        try {
            $currentUser = $this->security->getUser();

            if (!$currentUser) {
                return new JsonResponse('Unauthorized', JsonResponse::HTTP_UNAUTHORIZED);
            }

            $vacationDTO->reviewedBy = $this->userManager->getUserByEmail($currentUser->getUserIdentifier());

            $vacation = $this->vacationManager->rejectVacationRequest($id, $vacationDTO);

            if ($vacation === null) {
                return new JsonResponse('Vacation not found', JsonResponse::HTTP_NOT_FOUND);
            };

            return new JsonResponse(
                $this->serializer->serialize($vacation, 'json'),
                JsonResponse::HTTP_OK,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }

        // TODO: reikes sukurus siust per maileri notificationa adminams
    }

    #[Route('/api/admin/confirm-vacation/{id}', name: 'confirm_vacation', methods: ['PATCH'])]
    public function confirmVacationRequest(
        Request $request,
        string $id,
        #[MapRequestPayload()] VacationDTO $vacationDTO
    ) {
        try {
            $currentUser = $this->security->getUser();

            if (!$currentUser) {
                return new JsonResponse('Unauthorized', JsonResponse::HTTP_UNAUTHORIZED);
            }

            $vacationDTO->reviewedBy = $this->userManager->getUserByEmail($currentUser->getUserIdentifier());

            $vacation = $this->vacationManager->confirmVacationRequest($id, $vacationDTO);

            if ($vacation === null) {
                return new JsonResponse('Vacation not found', JsonResponse::HTTP_NOT_FOUND);
            };

            return new JsonResponse(
                $this->serializer->serialize($vacation, 'json'),
                JsonResponse::HTTP_OK,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }
}
