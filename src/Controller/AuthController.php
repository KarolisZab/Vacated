<?php

namespace App\Controller;

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
use Google\Client;
use Google\Service\Oauth2;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private UserManager $userManager,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private Security $security
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

    #[Route('/api/google-login', name: 'google_login', methods:['GET'])]
    public function googleLogin(Client $googleClient): Response
    {
        // setAuthConfig is used for setting up google oauth client
        $googleClient->setAuthConfig('/var/www/html/config/google_credentials.json');
        $googleClient->addScope('email');

        $authUrl = $googleClient->createAuthUrl();

        // redirects user to the google oauth consent screen
        return new RedirectResponse($authUrl);
    }

    #[Route('/api/google-callback', name: 'google_callback', methods:['GET'])]
    public function googleCallback(Request $request, Client $googleClient, JwtIssuer $jwtIssuer): Response
    {
        // handles google oauth callback
        $code = $request->query->get('code');

        // exchanges authorization code for an access token
        $accessToken = $googleClient->fetchAccessTokenWithAuthCode($code);

        // uses the access token to fetch user information
        $googleClient->setAccessToken($accessToken);
        $googleService = new Oauth2($googleClient);
        $userInfo = $googleService->userinfo->get();

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userInfo['email']]);

        if (!$user) {
            return new JsonResponse('User does not exist', JsonResponse::HTTP_CONFLICT);
        }

        // Issue a JWT token for the user
        $token = $jwtIssuer->issueToken([
            'email' => $userInfo['email'],
            'roles' => $user->getRoles()
        ]);

        return new JsonResponse(['email' => $user->getEmail(), 'roles' => $user->getRoles(), 'access_token' => $token]);
    }
}
