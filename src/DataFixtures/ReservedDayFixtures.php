<?php

namespace App\DataFixtures;

use App\Entity\ReservedDay;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservedDayFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('admin_user2');

        $dateFrom = (new \DateTimeImmutable())->modify('+15 days');
        $dateTo = $dateFrom->modify('+1 day');

        $reservedDay = new ReservedDay();
        $reservedDay
            ->setReservedBy($user)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom->format('Y-m-d')))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo->format('Y-m-d')))
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

    public static function getGroups(): array
    {
        return ['test'];
    }
}
