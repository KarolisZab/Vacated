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

    #[Route('/api/admin/all-confirmed-days', name: 'get_all_confirmed_days_count', methods: ['GET'])]
    public function getConfirmedVacationDaysCount()
    {
        try {
            $confirmedVacationDays = $this->vacationManager->getConfirmedVacationDaysInYear();

            return new JsonResponse($confirmedVacationDays, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/admin/all-vacations', name: 'get_all_vacations', methods: ['GET'])]
    public function getAllVacations(Request $request)
    {
        // $page = $request->query->get('page', 1);
        // $limit = $request->query->get('limit', 10);
        $filter = $request->query->get('vacationType');

        // $vacations = $this->vacationManager->getVacations($limit, ($page - 1) * $limit);
        // $vacationCount = $this->vacationManager->getVacationsCount();
        $allVacations = $this->vacationManager->getAllVacations($filter);


        // $results = ['totalItems' => $vacationCount, 'items' => $vacations];

        return new JsonResponse($this->serializer->serialize($allVacations, 'json'), JsonResponse::HTTP_OK, [], true);
        // return new JsonResponse($this->serializer->serialize($results, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/admin/pending-vacations', name: 'get_unconfirmed_and_not_rejected_vacations_count', methods: ['GET'])]
    public function getPendingVacationsCount()
    {
        try {
            $count = $this->vacationManager->getPendingVacationRequestCount();

            return new JsonResponse($count, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
