<?php

namespace App\Controller\Admin;

use App\DTO\UserDTO;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin')]
class UserController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private UserManager $userManager
    ) {
    }

    #[Route('/users', name: 'get_all_users', methods: ['GET'])]
    public function getAllUsers(Request $request)
    {
        $allUsers = $this->userManager->getAllUsers();

        return new JsonResponse($this->serializer->serialize($allUsers, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    public function getOneUser(string $id)
    {
        $user = $this->userManager->getUser($id);

        if ($user === null) {
            return new JsonResponse('User not found', JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($user, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(Request $request, string $id)
    {
        try {
            $user = $this->userManager->deleteUser($id);

            if ($user === false) {
                return new JsonResponse('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse($this->serializer->serialize($user, 'json'), JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PATCH'])]
    public function updateUser(Request $request, string $id, #[MapRequestPayload()] UserDTO $userDTO)
    {
        try {
            $user = $this->userManager->updateUser($id, $userDTO);

            if ($user === null) {
                return new JsonResponse('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse($this->serializer->serialize($user, 'json'), JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), $exception->getCode());
        }
    }
}
