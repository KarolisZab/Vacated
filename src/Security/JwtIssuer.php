<?php

namespace App\Security;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Psr\Clock\ClockInterface;

class JwtIssuer
{
    private Configuration $jwtConfiguration;
    private ClockInterface $clock;

    public function __construct(string $jwtSecretKey, ClockInterface $clock)
    {
        $this->jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($jwtSecretKey)
        );
        $this->clock = $clock;
    }

    public function issueToken(
        array $claims,
        int $ttl = 3600
    ): string {
        $builder = $this->jwtConfiguration->builder();

        foreach ($claims as $claim => $value) {
            $builder = $builder->withClaim($claim, $value);
        }

        $issuedAt = $this->clock->now();
        $expiresAt = $issuedAt->modify("+$ttl seconds");

        $builder = $builder->issuedAt($issuedAt)
                        ->expiresAt($expiresAt);

        $token = $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());

        return $token->toString();
    }

    public function getJwtConfiguration(): Configuration
    {
        return $this->jwtConfiguration;
    }
}
