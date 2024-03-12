<?php

namespace App\Controller\API;

use App\DTO\VacationDTO;
use App\Service\UserManager;
use App\Service\VacationManager;
use Doctrine\ORM\EntityManagerInterface;
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
        private EntityManagerInterface $entityManager,
        private VacationManager $vacationManager,
        private SerializerInterface $serializer,
        private Security $security
    ) {
    }

    #[Route('/api/request-vacation', name: 'create_vacation', methods: ['POST'])]
    public function requestVacation(Request $request, #[MapRequestPayload()] VacationDTO $vacationDTO)
    {
        try {
            $currentUser = $this->security->getUser();

            if (!$currentUser) {
                return new JsonResponse('Failed to authorize', JsonResponse::HTTP_UNAUTHORIZED);
            }

            $user = $this->userManager->getUserByEmail($currentUser->getUserIdentifier());

            $vacation = $this->vacationManager->requestVacation($user, $vacationDTO);

            if ($vacation === null) {
                return new JsonResponse('Failed to create vacation request', JsonResponse::HTTP_BAD_REQUEST);
            };

            return new JsonResponse(
                $this->serializer->serialize($vacation, 'json'),
                JsonResponse::HTTP_CREATED,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }

        // TODO: reikes sukurus siust per maileri notificationa adminams
    }

    #[Route('/api/update-vacation/{id}', name: 'update_vacation', methods: ['PATCH'])]
    public function updateVacation(Request $request, string $id, #[MapRequestPayload()] VacationDTO $vacationDTO)
    {
        try {
            $currentUser = $this->security->getUser();

            if (!$currentUser) {
                return new JsonResponse('Failed to authorize', JsonResponse::HTTP_UNAUTHORIZED);
            }

            $user = $this->userManager->getUserByEmail($currentUser->getUserIdentifier());

            $vacation = $this->vacationManager->updateRequestedVacation($id, $user, $vacationDTO);

            if ($vacation === null) {
                return new JsonResponse('Vacation request not found', JsonResponse::HTTP_NOT_FOUND);
            };

            return new JsonResponse(
                $this->serializer->serialize($vacation, 'json'),
                JsonResponse::HTTP_OK,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }
    }

    #[Route('/api/vacations/{id}', name: 'get_vacation', methods: ['GET'])]
    public function getVacation(string $id)
    {
        $vacation = $this->vacationManager->getVacationRequest($id);

        if ($vacation === null) {
            return new JsonResponse('Vacation not found', JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($vacation, 'json'), JsonResponse::HTTP_OK, [], true);
    }
}