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
}
