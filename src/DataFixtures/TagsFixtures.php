<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TagsFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $tag1 = new Tag();
        $tag1
            ->setName('Backend')
            ->setColorCode('#990000');
        $manager->persist($tag1);
        $this->addReference('Backend', $tag1);

        $tag2 = new Tag();
        $tag2
            ->setName('Frontend')
            ->setColorCode('#FF9999');
        $manager->persist($tag2);
        $this->addReference('Frontend', $tag2);

        $tag3 = new Tag();
        $tag3
            ->setName('Devops')
            ->setColorCode('#953a3a');
        $manager->persist($tag3);
        $this->addReference('Devops', $tag3);

        $tag4 = new Tag();
        $tag4
            ->setName('Tester')
            ->setColorCode('#a6ec56');
        $manager->persist($tag4);
        $this->addReference('Tester', $tag4);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['dataset'];
    }
}
