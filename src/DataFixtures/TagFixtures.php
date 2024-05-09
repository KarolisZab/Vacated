<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $tag1 = new Tag();
        $tag1
            ->setName('Backend')
            ->setColorCode('#990000');
        $manager->persist($tag1);

        $tag2 = new Tag();
        $tag2
            ->setName('Frontend')
            ->setColorCode('#FF9999');
        $manager->persist($tag2);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['test'];
    }
}
