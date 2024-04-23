<?php

namespace App\Controller\API;

use App\Service\UserManager;
use App\Trait\LoggerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    use LoggerTrait;

    public function __construct(
        private SerializerInterface $serializer,
        private UserManager $userManager,
        private Security $security
    ) {
    }

    #[Route('/api/user-available-days', name: 'get_users_available_days', methods: ['GET'])]
    public function getCurrentUsersAvailableDays(Request $request)
    {
        try {
            $currentUser = $this->security->getUser();

            $user = $this->userManager->getUserByEmail($currentUser->getUserIdentifier());
            $userEmail = $user->getEmail();

            $count = $this->userManager->getAvailableDaysForUser($userEmail);

            return new JsonResponse($count, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
