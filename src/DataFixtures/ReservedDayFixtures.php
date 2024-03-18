<?php

namespace App\DataFixtures;

use App\Entity\ReservedDay;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservedDayFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('admin_user2');

        $reservedDay = new ReservedDay();
        $reservedDay
            ->setReservedBy($user)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-04-19'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-04-20'))
            ->setNote('Important launch');
        $manager->persist($reservedDay);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
