<?php

namespace App\Service\GoogleOAuth;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface GoogleOAuthInterface
{
    public function loginWithGoogle(?string $code): RedirectResponse | JsonResponse;

    public function createAuthUrl(): string;
}
