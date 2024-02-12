<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Support\FunctionalTester;

class CreateAdminCest
{
    public function testCreateUser(FunctionalTester $I)
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);

        /** @var \App\Repository\UserRepository $repository */
        $repository = $em->getRepository(User::class);

        $usersBefore = $repository->findBy(['username' => 'Nemoksa']);

        $I->assertEquals(0, count($usersBefore));

        /** @var UserManager $um */
        $um = $I->grabService(UserManager::class);
        $um->createAdmin('Nemoksa', 'karolis@karolis', 'testas');

        $users = $repository->findBy(['username' => 'Nemoksa']);

        $I->assertEquals(1, count($users));
    }

    public function testDeleteAdmin(FunctionalTester $I)
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);

        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);

        /** @var UserManager $um */
        $um = $I->grabService(UserManager::class);
        $um->createAdmin('Admin', 'admin@admin.lt', 'Admin');

        $usersBefore = $userRepository->findBy(['username' => 'Admin']);
        $I->assertEquals(1, count($usersBefore));

        $um->deleteAdmin('Admin');

        $usersAfter = $userRepository->findBy(['username' => 'admin']);
        $I->assertEquals(0, count($usersAfter));

        $um->createAdmin('Adminas', 'adminas@adminas.com', 'admin');
        $usersBefore2 = $userRepository->findBy(['email' => 'adminas@adminas.com']);

        $I->assertEquals(1, count($usersBefore2));

        $um->deleteAdmin('adminas@adminas.com');

        $usersAfter2 = $userRepository->findBy(['email' => 'adminas@adminas.com']);
        $I->assertEquals(0, count($usersAfter2));

        $um->createAdmin('testas', 'testas@admin.com', 'test');
        $userBefore3 = $userRepository->findBy(['username' => 'testas']);

        $I->assertEquals(1, count($userBefore3));

        try {
            $um->deleteUser(4);
        } catch (\Exception $e) {
        }

        $userAfter3 = $userRepository->findBy(['username' => 'testas']);
        $I->assertEquals(1, count($userAfter3));
    }

    public function testUpdateUser(FunctionalTester $I)
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);

        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);

        /** @var UserManager $um */
        $um = $I->grabService(UserManager::class);

        $um->createAdmin('testinis', 'testinis@tes.com', 'test');
        $user = $userRepository->findOneBy(['username' => 'testinis']);

        $updateData = [
            'first_name' => 'Karolis',
            'last_name' => 'Testinis',
            'phone_number' => '123456789'
        ];

        // $updateData2 = [
        //     'first_name' => 'Karolis',
        //     'last_name' => '',
        //     'phone_number' => '123456789'
        // ];

        try {
            $um->updateUser($user->getId(), $updateData);
        } catch (\Exception $e) {
        }

        $I->assertEquals('Karolis', $user->getFirstName());
        $I->assertEquals('Testinis', $user->getLastName());
        $I->assertEquals('123456789', $user->getPhoneNumber());
    }
}
