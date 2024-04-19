<?php

namespace App\Service\GoogleOAuth;

use Symfony\Component\HttpFoundation\JsonResponse;

class MockedGoogleOAuthService implements GoogleOAuthInterface
{
    public function loginWithGoogle(?string $code): JsonResponse
    {
        return new JsonResponse();
    }

    public function createAuthUrl(): string
    {
        return '';
    }
}
