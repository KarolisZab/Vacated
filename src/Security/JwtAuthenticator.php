<?php

namespace App\Security;

use App\Entity\User;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class JwtAuthenticator extends AbstractAuthenticator
{
    private UserManager $userManager;
    private JwtValidator $jwtValidator;

    public function __construct(UserManager $userManager, JwtValidator $jwtValidator)
    {
        $this->userManager = $userManager;
        $this->jwtValidator = $jwtValidator;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization')
            && strpos($request->headers->get('Authorization'), 'Bearer') === 0;
    }

    public function authenticate(Request $request): Passport
    {
        $jwtToken = $this->extractJwtToken($request);

        if (!$jwtToken) {
            throw new AuthenticationException('Invalid JWT token');
        }

        $email = $this->validateJwtToken($jwtToken);

        $user = $this->userManager->getUserByEmail($email);

        if (!$user instanceof User) {
            throw new AuthenticationException('User not found');
        }

        return new SelfValidatingPassport(new UserBadge($user->getEmail()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => 'Authentication failed'], Response::HTTP_UNAUTHORIZED);
    }

    private function extractJwtToken(Request $request): ?string
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader) {
            return null;
        }

        return trim(str_replace('Bearer', '', $authorizationHeader));
    }

    private function validateJwtToken(string $jwtToken): string
    {
        $user = $this->jwtValidator->validateToken($jwtToken);

        if (!$user) {
            throw new AuthenticationException('Token validation failed');
        }

        return $user;
    }
}
