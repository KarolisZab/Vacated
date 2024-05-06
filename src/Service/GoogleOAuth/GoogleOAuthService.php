<?php

namespace App\Service\GoogleOAuth;

use App\Entity\User;
use App\Security\JwtIssuer;
use App\Trait\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Oauth2;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleOAuthService implements GoogleOAuthInterface
{
    use LoggerTrait;

    public function __construct(
        private Client $googleClient,
        private JwtIssuer $jwtIssuer,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function loginWithGoogle(?string $code): RedirectResponse | JsonResponse
    {
        if (null === $code) {
            return new RedirectResponse('/error-page');
        }

        try {
            // exchanges authorization code for an access token
            $accessToken = $this->googleClient->fetchAccessTokenWithAuthCode($code);

            // uses the access token to fetch user information
            $this->googleClient->setAccessToken($accessToken);
            $googleService = new Oauth2($this->googleClient);
            $userInfo = $googleService->userinfo->get();
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . $e->getMessage());
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userInfo['email']]);

        if (!$user) {
            // Redirect page
            return new RedirectResponse('/error-page');
        }

        $token = $this->jwtIssuer->issueToken([
            'email' => $userInfo['email'],
            'roles' => $user->getRoles()
        ]);

        return new JsonResponse([
            'access_token' => $token,
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName()
        ]);
    }

    public function createAuthUrl(): string
    {
        // setAuthConfig is used for setting up google oauth client
        $this->googleClient->addScope('email');

        return $this->googleClient->createAuthUrl();
    }
}
