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
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-04-17'));

        $manager->persist($vacation);

        $vacation = new Vacation();
        $vacation->setRequestedBy($user)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-03-13'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-03-15'));

        $manager->persist($vacation);

        $vacation = new Vacation();
        $vacation->setRequestedBy($user)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-03-01'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-03-05'))
            ->setConfirmed(true);

        $manager->persist($vacation);

        $vacation = new Vacation();
        $vacation->setRequestedBy($user)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-03-04'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-03-07'));

        $manager->persist($vacation);

        $manager->flush();
    }
}
