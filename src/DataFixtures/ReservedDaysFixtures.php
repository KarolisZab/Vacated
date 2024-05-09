<?php

namespace App\DataFixtures;

use App\Entity\ReservedDay;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservedDaysFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('admin_user2');
        $user2 = $this->getReference('admin_user1');
        $user3 = $this->getReference('admin_user3');

        $tag1 = $this->getReference('Backend');
        $tag2 = $this->getReference('Frontend');
        $tag3 = $this->getReference('Devops');
        $tag4 = $this->getReference('Tester');

        $dateFrom = (new \DateTimeImmutable('2024-05-13'));
        $dateTo = $dateFrom->modify('+1 day');

        $reservedDay = new ReservedDay();
        $reservedDay
            ->setReservedBy($user)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom->format('Y-m-d')))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo->format('Y-m-d')))
            ->setNote('Launching new project.')
            ->setTags(new ArrayCollection([$tag2]));
        $manager->persist($reservedDay);

        $dateFrom = (new \DateTimeImmutable('2024-05-22'));
        $dateTo = $dateFrom->modify('+2 day');

        $reservedDay2 = new ReservedDay();
        $reservedDay2
            ->setReservedBy($user2)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom->format('Y-m-d')))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo->format('Y-m-d')))
            ->setNote('Company trip.')
            ->setTags(new ArrayCollection([$tag1]));
        $manager->persist($reservedDay2);

        $dateFrom = (new \DateTimeImmutable('2024-06-21'));
        $dateTo = $dateFrom->modify('+2 day');

        $reservedDay3 = new ReservedDay();
        $reservedDay3
            ->setReservedBy($user3)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom->format('Y-m-d')))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo->format('Y-m-d')))
            ->setNote('Meeting with new clients.');
        $manager->persist($reservedDay3);

        $dateFrom = (new \DateTimeImmutable('2024-07-01'));
        $dateTo = $dateFrom->modify('+4 day');

        $reservedDay4 = new ReservedDay();
        $reservedDay4
            ->setReservedBy($user3)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom->format('Y-m-d')))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo->format('Y-m-d')))
            ->setNote('Meeting with new clients.')
            ->setTags(new ArrayCollection([$tag4]));
        $manager->persist($reservedDay4);

        $dateFrom = (new \DateTimeImmutable('2024-08-05'));
        $dateTo = $dateFrom->modify('+4 day');

        $reservedDay5 = new ReservedDay();
        $reservedDay5
            ->setReservedBy($user3)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom->format('Y-m-d')))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo->format('Y-m-d')))
            ->setNote('Road trip.')
            ->setTags(new ArrayCollection([$tag3, $tag1]));
        $manager->persist($reservedDay5);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TagsFixtures::class,
            UsersFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['dataset'];
    }
}
