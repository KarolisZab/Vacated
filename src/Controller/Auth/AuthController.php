<?php

namespace App\Controller\Auth;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Security\JwtIssuer;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AuthController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private UserManager $userManager,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/api/register', name: 'user_register')]
    public function register(Request $request, #[MapRequestPayload()] UserDTO $userDTO)
    {
        $user = $this->userManager->createUser($userDTO);

        if ($user !== null) {
            return new JsonResponse(
                $this->serializer->serialize($user, 'json'),
                JsonResponse::HTTP_CREATED,
                [],
                true
            );
        } else {
            return new JsonResponse('User with this email already exists', JsonResponse::HTTP_CONFLICT);
        }
    }

    #[Route('/api/login', name: 'user_login', methods:['POST'])]
    public function login(Request $request, JwtIssuer $jwtIssuer)
    {
        $credentials = json_decode($request->getContent(), true);

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user instanceof UserInterface) {
            return new JsonResponse('Invalid email or password credentials', 400);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $credentials['password'])) {
            return new JsonResponse('Invalid email or password credentials', 400);
        }

        $token = $jwtIssuer->issueToken([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ]);

        return new JsonResponse(['email' => $user->getEmail(), 'roles' => $user->getRoles(), 'access_token' => $token]);
    }
}
