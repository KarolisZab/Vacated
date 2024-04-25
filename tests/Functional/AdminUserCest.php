<?php

namespace App\Tests\Functional;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Support\FunctionalTester;

class CreateAdminCest
{
    private EntityManagerInterface $entityManager;
    private UserManager $userManager;

    public function _before(FunctionalTester $I)
    {
        $this->entityManager = $I->grabService(EntityManagerInterface::class);
        $this->userManager = $I->grabService(UserManager::class);
    }

    public function testCreateAdminUser(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        $usersBefore = $repository->findBy(['email' => 'karolis@karolis']);

        $I->assertEquals(0, count($usersBefore));

        $this->userManager->createAdmin('karolis@karolis', 'testas');
        $user = $repository->findOneBy(['email' => 'karolis@karolis']);

        $I->assertNotNull($user);
        $I->assertTrue($user->isAdmin());
    }

    public function testCreateAdminUserIfUserWithEmailAlreadyExists(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $repository */
        // $repository = $this->entityManager->getRepository(User::class);

        $this->userManager->createAdmin('karolis@karolis', 'testas');
        $user = $this->userManager->createAdmin('karolis@karolis', 'test');

        $I->assertNull($user, "User not created, because user with the same email already exists");
    }

    public function testDeleteAdmin(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $this->userManager->createAdmin('admin@admin.lt', 'Admin');

        $this->userManager->deleteAdmin('admin@admin.lt');
        $usersAfter = $userRepository->findBy(['email' => 'admin@admin.lt']);

        $I->assertEquals(0, count($usersAfter));
    }

    public function testAdminCannotDeleteAdmin(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $admin = $this->userManager->createAdmin('testas33@admin.com', 'test');

        try {
            $this->userManager->deleteUser($admin->getId());
        } catch (\Exception) {
        }

        $userAfter3 = $userRepository->findBy(['email' => 'testas33@admin.com']);
        $I->assertEquals(1, count($userAfter3));
    }

    public function testDeleteNonExistingAdmin(FunctionalTester $I)
    {
        $result = $this->userManager->deleteAdmin('Adminas');

        $I->assertFalse($result);
    }

    public function testDeleteNonExistingUser(FunctionalTester $I)
    {
        $result = $this->userManager->deleteUser('30');

        $I->assertFalse($result);
    }

    public function testUpdateUser(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $this->userManager->createAdmin('testinis@test.com', 'test');
        $user = $userRepository->findOneBy(['email' => 'testinis@test.com']);

        $updateDTO = new UserDTO('', 'Karolis', 'Testinis', '123456789', '');

        $this->userManager->updateUser($user->getId(), $updateDTO);

        $updatedUser = $userRepository->findOneBy(['email' => 'testinis@test.com']);

        $I->assertEquals('Karolis', $updatedUser->getFirstName());
        $I->assertEquals('Testinis', $updatedUser->getLastName());
        $I->assertEquals('123456789', $updatedUser->getPhoneNumber());
    }

    public function testUpdateNonExistingUser(FunctionalTester $I)
    {
        $updateDTO = new UserDTO('', 'Karolis', 'Testinis', '123456789', '');

        $result = $this->userManager->updateUser('333', $updateDTO);

        $I->assertNull($result);
    }

    public function testUserCreate(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        $usersBefore = $repository->findBy(['email' => 'registrationtest@test.com']);

        $I->assertEquals(0, count($usersBefore));

        $userDto = new UserDTO(
            'registrationtest@test.com',
            'registrationtest',
            'Karolis',
            'Zabinskis',
            '123456789'
        );
        $this->userManager->createUser($userDto);

        $usersAfter = $repository->findBy(['email' => 'registrationtest@test.com']);

        $I->assertEquals(1, count($usersAfter));
    }

    public function testUserCreateIfUserWithEmailAlreadyExist(FunctionalTester $I)
    {
        $existingUserDto = new UserDTO(
            'registrationtest@test.com',
            'existinguser',
            'Existing',
            'User',
            '123456789'
        );
        $this->userManager->createUser($existingUserDto);

        $userDto = new UserDTO(
            'registrationtest@test.com',
            'regtest',
            'Karo',
            'Lis',
            '123456789'
        );

        $user = $this->userManager->createUser($userDto);

        $I->assertNull($user, "User not created, because user with the same email already exists");
    }

    public function testGetUserThatDoesNotExist(FunctionalTester $I)
    {
        $result = $this->userManager->getUser('333');
        $I->assertNull($result);

        $result = $this->userManager->getUserByEmail('test@testget');
        $I->assertNull($result);
    }
}
