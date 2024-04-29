<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Security\JwtIssuer;
use App\Service\GoogleOAuth\GoogleOAuthInterface;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Trait\LoggerTrait;

class AuthController extends AbstractController
{
    use LoggerTrait;

    /**
     * @param \App\Service\GoogleOAuth\GoogleOAuthService $googleService
     */
    public function __construct(
        private SerializerInterface $serializer,
        private UserManager $userManager,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JwtIssuer $jwtIssuer,
        private GoogleOAuthInterface $googleService
    ) {
    }

    #[Route('/api/admin/create-user', name: 'user_register')]
    public function createUser(Request $request, #[MapRequestPayload()] UserDTO $userDTO)
    {
        try {
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
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }

    #[Route('/api/login', name: 'user_login', methods:['POST'])]
    public function login(Request $request, JwtIssuer $jwtIssuer)
    {
        $credentials = json_decode($request->getContent(), true);

        if (isset($credentials['google_code'])) {
            return $this->googleService->loginWithGoogle($credentials['google_code']);
        }

        return $this->loginWithCredentials($credentials['email'], $credentials['password']);
    }

    private function loginWithCredentials(string $username, string $password): Response
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $username]);

        if (!$user instanceof UserInterface) {
            return new JsonResponse('Invalid email or password credentials', 400);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse('Invalid email or password credentials', 400);
        }

        $token = $this->jwtIssuer->issueToken([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ]);

        return new JsonResponse([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'access_token' => $token
        ]);
    }

    #[Route('/oauth', name: 'google_login', methods:['GET'])]
    public function googleLogin(): Response
    {
        // redirects user to the google oauth consent screen
        return new RedirectResponse($this->googleService->createAuthUrl());
    }
}
