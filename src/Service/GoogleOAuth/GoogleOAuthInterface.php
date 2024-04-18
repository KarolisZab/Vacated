<?php

namespace App\Service\GoogleOAuth;

use Symfony\Component\HttpFoundation\JsonResponse;

interface GoogleOAuthInterface
{
    public function loginWithGoogle(?string $code): JsonResponse;

    public function createAuthUrl(): string;
}
