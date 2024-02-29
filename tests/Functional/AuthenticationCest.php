<?php

namespace App\Tests\Functional;

use App\DTO\UserDTO;
use App\Service\UserManager;
use App\Utils\MockedClock;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\Clock;
use Tests\Support\FunctionalTester;

class AuthenticationCest
{
    private EntityManagerInterface $entityManager;
    private UserManager $userManager;

    public function _before(FunctionalTester $I)
    {
        $this->entityManager = $I->grabService(EntityManagerInterface::class);
        $this->userManager = $I->grabService(UserManager::class);
        $this->userManager->createAdmin('jwttest@test.com', 'test');
    }

    public function testSuccessfulUserLogin(FunctionalTester $I)
    {
        $I->authenticateUser('jwttest@test.com', 'test');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    public function testUserLoginWithWrongPassword(FunctionalTester $I)
    {
        $I->authenticateUser('jwttest@test.com', 'fail');
        $I->seeResponseCodeIs(400);
    }

    public function testUserLoginWithWrongEmail(FunctionalTester $I)
    {
        $I->authenticateUser('jwttestwrong@test.com', 'test');
        $I->seeResponseCodeIs(400);
    }

    public function testIfUserIsAuthenticatedToGetUsers(FunctionalTester $I)
    {
        $I->sendRequest('get', '/api/admin/users');
        $I->seeResponseCodeIs(401);

        $token = $I->grabTokenForUser('jwttest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('get', '/api/admin/users');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function testIfUserIsAuthenticatedToGetOneUser(FunctionalTester $I)
    {
        /** @var User $user */
        $user = $this->userManager->getUserByEmail('jwttest@test.com');

        $I->sendRequest('get', '/api/admin/users/' . $user->getId());
        $I->seeResponseCodeIs(401);

        $token = $I->grabTokenForUser('jwttest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('get', '/api/admin/users/' . $user->getId());

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function testIfAdminIsAuthenticatedToUpdateUser(FunctionalTester $I)
    {
        /** @var User $user */
        $user = $this->userManager->getUserByEmail('jwttest@test.com');

        $updateDTO = new UserDTO('', '', 'Karolis', 'Zabinskis', '123456789');

        $I->sendRequest('patch', '/api/admin/update-user/' . $user->getId(), [
            'firstName' => $updateDTO->firstName,
            'lastName' => $updateDTO->lastName,
            'phoneNumber' => $updateDTO->phoneNumber
        ]);

        $I->seeResponseCodeIs(401);

        $token = $I->grabTokenForUser('jwttest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('patch', '/api/admin/update-user/' . $user->getId(), [
            'firstName' => $updateDTO->firstName,
            'lastName' => $updateDTO->lastName,
            'phoneNumber' => $updateDTO->phoneNumber
        ]);

        $I->seeResponseContainsJson([
            'firstName' => 'Karolis',
            'lastName' => 'Zabinskis',
            'phoneNumber' => '123456789'
        ]);
        $I->seeResponseCodeIs(200);
    }

    public function testIfAdminIsAuthenticatedToDeleteUser(FunctionalTester $I)
    {
        $userDto = new UserDTO('regtest@test.com', 'regtest', 'Karolis', 'Zabinskis', '123456789');

        $this->userManager->createUser($userDto);

        /** @var User $user */
        $user = $this->userManager->getUserByEmail('regtest@test.com');

        $I->sendRequest('delete', '/api/admin/delete-user/' . $user->getId());
        $I->seeResponseCodeIs(401);

        $token = $I->grabTokenForUser('jwttest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('delete', '/api/admin/delete-user/' . $user->getId());
        $I->seeResponseCodeIs(200);
    }

    public function testIfAdminIsAuthenticatedToDeleteAdmin(FunctionalTester $I)
    {
        /** @var User $user */
        $user = $this->userManager->getUserByEmail('jwttest@test.com');

        $I->sendRequest('delete', '/api/admin/delete-user/' . $user->getId());
        $I->seeResponseCodeIs(401);

        $token = $I->grabTokenForUser('jwttest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('delete', '/api/admin/delete-user/' . $user->getId());
        $I->seeResponseCodeIs(403);
    }

    public function testTokenExpirationValidation(FunctionalTester $I)
    {
        $clock = new MockedClock();
        Clock::set($clock);

        $this->userManager->createAdmin('expiration@test', 'test');

        $I->authenticateUser('expiration@test', 'test');

        $token = $I->grabTokenForUser('expiration@test');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('get', '/api/admin/users');
        $I->seeResponseCodeIs(200);

        $clock->set('+2 hours');

        $I->sendRequest('get', '/api/admin/users');
        $I->seeResponseCodeIs(401);
    }
}
