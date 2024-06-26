<?php

namespace App\Tests\Functional;

use App\DTO\UserDTO;
use App\Security\JwtIssuer;
use App\Security\JwtValidator;
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
    private JwtIssuer $jwtIssuer;
    private JwtValidator $jwtValidator;

    public function _before(FunctionalTester $I)
    {
        $this->entityManager = $I->grabService(EntityManagerInterface::class);
        $this->userManager = $I->grabService(UserManager::class);
        $this->jwtIssuer = $I->grabService(JwtIssuer::class);
        $this->jwtValidator = $I->grabService(JwtValidator::class);
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

        $updateDTO = new UserDTO('', 'Karolis', 'Zabinskis', '123456789', 20, '');

        $I->sendRequest('patch', '/api/admin/users/' . $user->getId(), [
            'firstName' => $updateDTO->firstName,
            'lastName' => $updateDTO->lastName,
            'phoneNumber' => $updateDTO->phoneNumber
        ]);

        $I->seeResponseCodeIs(401);

        $token = $I->grabTokenForUser('jwttest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('patch', '/api/admin/users/' . $user->getId(), [
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
        /** @var User $user */
        $user = $this->userManager->getUserByEmail('regtest@test.com');

        $I->sendRequest('delete', '/api/admin/users/' . $user->getId());
        $I->seeResponseCodeIs(401);

        $token = $I->grabTokenForUser('jwttest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('delete', '/api/admin/users/' . $user->getId());
        $I->seeResponseCodeIs(200);
    }

    public function testIfAdminIsAuthenticatedToDeleteAdmin(FunctionalTester $I)
    {
        /** @var User $user */
        $user = $this->userManager->getUserByEmail('jwttest@test.com');

        $I->sendRequest('delete', '/api/admin/users/' . $user->getId());
        $I->seeResponseCodeIs(401);

        $token = $I->grabTokenForUser('jwttest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('delete', '/api/admin/users/' . $user->getId());
        $I->seeResponseCodeIs(403);
    }

    public function testTokenExpirationValidation(FunctionalTester $I)
    {
        $clock = new MockedClock();
        Clock::set($clock);

        $I->authenticateUser('expiration@test', 'test');

        $token = $I->grabTokenForUser('expiration@test');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('get', '/api/admin/users');
        $I->seeResponseCodeIs(200);

        $clock->set('+2 hours');

        $I->sendRequest('get', '/api/admin/users');
        $I->seeResponseCodeIs(401);
    }

    public function testCreateUserWithTags(FunctionalTester $I)
    {
        $userData = [
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumber' => '123456789',
            'tags' => [
                ['name' => 'Backend', 'colorCode' => '#990000'],
                ['name' => 'Frontend', 'colorCode' => '#FF9999']
            ],
        ];

        $token = $I->grabTokenForUser('jwttest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('post', '/api/admin/create-user', $userData);

        $I->seeResponseCodeIs(201);

        $I->seeResponseContainsJson([
            'email' => 'testuser@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumber' => '123456789',
            'tags' => [
                ['name' => 'Backend', 'colorCode' => '#990000'],
                ['name' => 'Frontend', 'colorCode' => '#FF9999'],
            ]
        ]);
    }

    public function testUpdateUserWithTags(FunctionalTester $I)
    {
        /** @var User $user */
        $user = $this->userManager->getUserByEmail('jwttest@test.com');

        $token = $I->grabTokenForUser('jwttest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('patch', '/api/admin/users/' . $user->getId(), [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumber' => '123456789',
            'tags' => [
                ['name' => 'Backend', 'colorCode' => '#990000'],
                ['name' => 'Frontend', 'colorCode' => '#FF9999']
            ],
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'email' => 'jwttest@test.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumber' => '123456789',
            'tags' => [
                ['name' => 'Backend', 'colorCode' => '#990000'],
                ['name' => 'Frontend', 'colorCode' => '#FF9999'],
            ]
        ]);

        $I->sendRequest('patch', '/api/admin/users/' . $user->getId(), [
            'email' => 'jwttest@test.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumber' => '123456789',
            'tags' => [['name' => 'Frontend', 'colorCode' => '#FF9999']],
        ]);

        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson([
            'tags' => ['name' => 'Backend', 'colorCode' => '#990000']
        ]);
    }

    public function testValidateResetToken(FunctionalTester $I)
    {
        $user = $this->userManager->getUserByEmail('jwttest@test.com');
        $token = $this->jwtIssuer->issueToken(['email' => 'jwttest@test.com', 'reset_token' => true]);

        $email = $this->jwtValidator->validateToken($token, true);

        $I->assertEquals($user->getEmail(), $email);
        $token = $token . 'asda';

        $I->expectThrowable(\Exception::class, function () use ($token) {
            $this->jwtValidator->validateToken($token, true);
        });

        $token = $this->jwtIssuer->issueToken(['email' => 'jwttest@test.com']);

        $I->expectThrowable(\Exception::class, function () use ($token) {
            $this->jwtValidator->validateToken($token, true);
        });
    }

    public function testResetPassword(FunctionalTester $I)
    {
        $token = $this->jwtIssuer->issueToken(['email' => 'jwttest@test.com', 'reset_token' => true]);
        $email = $this->jwtValidator->validateToken($token, true);

        $user = $this->userManager->getUserByEmail($email);

        $I->assertEquals('jwttest@test.com', $user->getEmail());

        $oldPassword = $user->getPassword();
        $this->userManager->changePassword($user, 'test');
        $newPassword = $user->getPassword();

        $I->assertNotEquals($oldPassword, $newPassword);

        $token = $token . 'asasd';
        $I->expectThrowable(\Exception::class, function () use ($token) {
            $this->jwtValidator->validateToken($token, true);
        });

        $token = $this->jwtIssuer->issueToken(['email' => 'jwttest@test.com']);

        $I->expectThrowable(\Exception::class, function () use ($token) {
            $this->jwtValidator->validateToken($token, true);
        });
    }
}
