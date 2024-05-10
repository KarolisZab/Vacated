<?php

namespace App\DataFixtures;

use App\Entity\Vacation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VacationsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('admin_user');
        $user2 = $this->getReference('admin_user2');
        $user3 = $this->getReference('user');
        $user4 = $this->getReference('user1');
        $user5 = $this->getReference('user2');
        $user6 = $this->getReference('user3');


        $vacation = new Vacation();
        $vacation->setRequestedBy($user3)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-05-13'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-05-17'))
            ->setConfirmed(true)
            ->setReviewedBy($user)
            ->setReviewedAt(new \DateTimeImmutable());

        $manager->persist($vacation);

        $vacation = new Vacation();
        $vacation->setRequestedBy($user2)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-05-29'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-06-05'))
            ->setConfirmed(true)
            ->setReviewedBy($user)
            ->setReviewedAt(new \DateTimeImmutable());

        $manager->persist($vacation);

        $vacation = new Vacation();
        $vacation->setRequestedBy($user)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-05-21'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-05-23'))
            ->setConfirmed(true)
            ->setReviewedBy($user2)
            ->setReviewedAt(new \DateTimeImmutable());

        $manager->persist($vacation);

        $vacation = new Vacation();
        $vacation->setRequestedBy($user4)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-05-28'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-05-31'))
            ->setConfirmed(true)
            ->setReviewedBy($user)
            ->setReviewedAt(new \DateTimeImmutable());

        $manager->persist($vacation);

        $vacation = new Vacation();
        $vacation->setRequestedBy($user5)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-06-04'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-06-06'))
            ->setConfirmed(true)
            ->setReviewedBy($user2)
            ->setReviewedAt(new \DateTimeImmutable());

        $manager->persist($vacation);

        $vacation = new Vacation();
        $vacation->setRequestedBy($user6)
            ->setDateFrom(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-07-15'))
            ->setDateTo(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-07-19'))
            ->setConfirmed(true)
            ->setReviewedBy($user)
            ->setReviewedAt(new \DateTimeImmutable());

        $manager->persist($vacation);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UsersFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['dataset'];
    }
}
