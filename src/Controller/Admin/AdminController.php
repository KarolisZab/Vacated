<?php

namespace App\Controller\Admin;

use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

#[Route('/api/admin')]
class AdminController extends AbstractController
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
        try {
            $user = $this->userManager->getUser($id);
            return new JsonResponse($this->serializer->serialize($user, 'json'), JsonResponse::HTTP_OK, [], true);
        } catch (UserNotFoundException $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }
    }

    #[Route('/delete-user/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(Request $request, string $id)
    {
        try {
            $user = $this->userManager->deleteUser($id);
            return new JsonResponse($this->serializer->serialize($user, 'json'), JsonResponse::HTTP_OK, [], true);
        } catch (UserNotFoundException $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        //TODO: Administratorius gali trinti kitus User, bet negali trinti kitu Admin useriu.
    }

    #[Route('/update-user/{id}', name: 'update_user', methods: ['PATCH'])]
    public function updateUser(Request $request, string $id)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            if ($parameters === null || empty($parameters)) {
                throw new \Exception('Invalid or empty request body', 400);
            }

            $user = $this->userManager->updateUser($id, $parameters);
            return new JsonResponse($this->serializer->serialize($user, 'json'), JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), 400);
        }
    }
}

//// lieka response ir iskvietimas funkcijos
// jeigu user === null, throwint exceptiona su status codu JsonResponse

// jeigu bando trint admina, reikia checko, ar jis yra adminas ar ne
// console command istrint admina ir vsio
