<?php

namespace App\Security;

use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Psr\Clock\ClockInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JwtValidator
{
    private Validator $jwtValidator;
    private JwtIssuer $jwtIssuer;
    private ClockInterface $clock;

    public function __construct(JwtIssuer $jwtIssuer, ClockInterface $clock)
    {
        $this->jwtValidator = new Validator();
        $this->jwtIssuer = $jwtIssuer;
        $this->clock = $clock;
    }

    public function validateToken(string $jwtToken): string
    {

        try {
            $parsedToken = $this->parseJwtToken($jwtToken);

            $signer = $this->jwtIssuer->getJwtConfiguration()->signer();
            $key = $this->jwtIssuer->getJwtConfiguration()->signingKey();
            $constraintSignedWith = new SignedWith($signer, $key);
            $constraintLooseValidAt = new LooseValidAt($this->clock);

            $email = $parsedToken->claims()->get('email');

            $this->jwtValidator->assert($parsedToken, $constraintSignedWith, $constraintLooseValidAt);

            return $email;
        } catch (\Exception) {
            throw new AuthenticationException('Token validation failed');
        }
    }

    private function parseJwtToken(string $jwtToken): Plain
    {
        /** @var \Lcobucci\JWT\Token\Plain $token */
        $token = $this->jwtIssuer->getJwtConfiguration()->parser()->parse($jwtToken);
        return $token;
    }
}
