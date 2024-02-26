<?php

namespace App\Security;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class JwtIssuer
{
    private Configuration $jwtConfiguration;

    public function __construct(string $jwtSecretKey)
    {
        $this->jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($jwtSecretKey)
        );
    }

    public function issueToken(
        array $claims,
        ?\DateTimeImmutable $issuedAt = null,
        ?\DateTimeImmutable $expiresAt = null
    ): string {
        $builder = $this->jwtConfiguration->builder();

        foreach ($claims as $claim => $value) {
            $builder = $builder->withClaim($claim, $value);
        }

        $issuedAt = $issuedAt ?? new \DateTimeImmutable();
        $expiresAt = $expiresAt ?? new \DateTimeImmutable('+1 hour');

        // $builder = $builder->issuedAt(new \DateTimeImmutable())
        //                 ->expiresAt(new \DateTimeImmutable('+1 hour'));

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
