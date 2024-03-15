<?php

namespace App\DataFixtures;

use App\Entity\Vacation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VacationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('admin_user');

        $vacation = new Vacation();
        $vacation->setRequestedBy($user)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-04-12'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-04-17'))
            ->setNote('');

        $manager->persist($vacation);
        $manager->flush();
    }
}
