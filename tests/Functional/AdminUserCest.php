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

    public function testCreateUser(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        $usersBefore = $repository->findBy(['username' => 'Nemoksa']);

        $I->assertEquals(0, count($usersBefore));

        $this->userManager->createAdmin('Nemoksa', 'karolis@karolis', 'testas');
        $users = $repository->findBy(['username' => 'Nemoksa']);

        $I->assertEquals(1, count($users));
    }

    public function testDeleteAdmin(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $this->userManager->createAdmin('Admin', 'admin@admin.lt', 'Admin');

        $this->userManager->deleteAdmin('admin@admin.lt');
        $usersAfter = $userRepository->findBy(['email' => 'admin@admin.lt']);

        $I->assertEquals(0, count($usersAfter));

        $this->userManager->createAdmin('Adminas', 'adminas@adminas.com', 'admin');

        $result = $this->userManager->deleteAdmin('Adminas');

        $I->assertFalse($result);
    }

    public function testAdminCannotDeleteAdmin(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $admin = $this->userManager->createAdmin('testas33', 'testas33@admin.com', 'test');

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

    public function testUpdateUser(FunctionalTester $I)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $this->userManager->createAdmin('testinis', 'testinis@tes.com', 'test');
        $user = $userRepository->findOneBy(['username' => 'testinis']);

        $updateDTO = new UserDTO('Karolis', 'Testinis', '123456789');

        $this->userManager->updateUser($user->getId(), $updateDTO);

        $updatedUser = $userRepository->findOneBy(['username' => 'testinis']);

        $I->assertEquals('Karolis', $updatedUser->getFirstName());
        $I->assertEquals('Testinis', $updatedUser->getLastName());
        $I->assertEquals('123456789', $updatedUser->getPhoneNumber());
    }
}
