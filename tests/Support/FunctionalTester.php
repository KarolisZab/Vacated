<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Entity\User;
use App\Security\JwtIssuer;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * Authenticates user and returns its access token on succes.
     * If authentication fails, it returns false.
     *
     * @param  string $email
     * @param  string $password
     *
     * @return string|false
     */
    public function authenticateUser(string $email, string $password): string|false
    {
        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->sendPost('/api/login', [
            'email' => $email,
            'password' => $password
        ]);

        $response = json_decode($this->grabResponse(), true);

        return $response['access_token'] ?? false;
    }

    public function grabTokenForUser(string $email): string
    {
        /** @var JwtIssuer $jwtIssuer */
        $jwtIssuer = $this->grabService(JwtIssuer::class);
        /** @var EntityManagerInterface $em */
        $em = $this->grabService(EntityManagerInterface::class);

        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \Exception('User not found in test');
        }

        return $jwtIssuer->issueToken([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ]);
    }

    public function sendRequest(string $methodType, string $url, array $data = [])
    {
        $this->haveHttpHeader('Content-Type', 'application/json');

        switch (strtoupper($methodType)) {
            case 'GET':
                // Codeception\REST sendGet method maps data to query params
                $this->sendGet($url, $data);
                break;
            case 'POST':
                $this->sendPost($url, $data);
                break;
            case 'PATCH':
                $this->sendPatch($url, $data);
                break;
            case 'DELETE':
                $this->sendDelete($url);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported HTTP method: $methodType");
        }
    }
}
