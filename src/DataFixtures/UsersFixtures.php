<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $tag1 = $this->getReference('Backend');
        $tag2 = $this->getReference('Frontend');

        $user = new User();
        $password = $this->passwordHasher->hashPassword($user, 'jonaitisjonas321user');
        $user->setEmail("jonas.jonaitis@email.com")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setIsAdmin(true)
            ->setFirstName('Jonas')
            ->setLastName('Jonaitis')
            ->setPhoneNumber('862461235')
            ->setAvailableDays(15)
            ->setTags(new ArrayCollection([$tag1]));
        $manager->persist($user);
        $this->addReference('user', $user);

        $admin2 = new User();
        $password = $this->passwordHasher->hashPassword($admin2, 'petraitispetras321admin');
        $admin2->setEmail("petras.petraitis@email.com")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setIsAdmin(true)
            ->setFirstName('Petras')
            ->setLastName('Petraitis')
            ->setPhoneNumber('862465423');
        $manager->persist($admin2);
        $this->addReference('admin_user3', $admin2);

        $admin3 = new User();
        $password = $this->passwordHasher->hashPassword($admin3, 'maraitismarius321admin');
        $admin3->setEmail("marius.maraitis@gmail.com")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setIsAdmin(true)
            ->setFirstName('Marius')
            ->setLastName('Maraitis')
            ->setPhoneNumber('866345275');
        $manager->persist($admin3);
        $this->addReference('admin_user1', $admin3);

        $admin4 = new User();
        $password = $this->passwordHasher->hashPassword($admin4, 'juozaitisjuozas321admin');
        $admin4->setEmail("juozas.juozaitis@email.com")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setIsAdmin(true)
            ->setFirstName('Juozas')
            ->setLastName('Juozaitis')
            ->setAvailableDays(14)
            ->setPhoneNumber('862465276');
        $manager->persist($admin4);
        $this->addReference('admin_user2', $admin4);

        $admin5 = new User();
        $password = $this->passwordHasher->hashPassword($admin5, 'karolyzas321');
        $admin5->setEmail("karzab@admin.com")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setIsAdmin(true)
            ->setFirstName('Karolis')
            ->setLastName('Zabinskis')
            ->setAvailableDays(17)
            ->setPhoneNumber('862461235');
        $manager->persist($admin5);
        $this->addReference('admin_user', $admin5);

        $user1 = new User();
        $password = $this->passwordHasher->hashPassword($user1, 'girenasdarius321user');
        $user1->setEmail("darius.girenas@email.com")
            ->setPassword($password)
            ->setRoles(['ROLE_USER'])
            ->setFirstName('Darius')
            ->setLastName('Girenas')
            ->setPhoneNumber('864542312')
            ->setAvailableDays(16)
            ->setTags(new ArrayCollection([$tag2]));
        $manager->persist($user1);
        $this->addReference('user1', $user1);

        $user2 = new User();
        $password = $this->passwordHasher->hashPassword($user2, 'karolenaskarolis321user');
        $user2->setEmail("karolis.karolenas@email.com")
            ->setPassword($password)
            ->setRoles(['ROLE_USER'])
            ->setFirstName('Karolis')
            ->setLastName('Karolenas')
            ->setAvailableDays(17)
            ->setPhoneNumber('862354782');
        $manager->persist($user2);
        $this->addReference('user2', $user2);

        $user3 = new User();
        $password = $this->passwordHasher->hashPassword($user3, 'laurinaitelaura321user');
        $user3->setEmail("laura.laurinaite@email.com")
            ->setPassword($password)
            ->setRoles(['ROLE_USER'])
            ->setFirstName('Laura')
            ->setLastName('Laurinaite')
            ->setPhoneNumber('862549723');
        $manager->persist($user3);
        $this->addReference('user3', $user3);

        $user4 = new User();
        $password = $this->passwordHasher->hashPassword($user4, 'monikamonikaite321user');
        $user4->setEmail("monika.monikaite@email.com")
            ->setPassword($password)
            ->setRoles(['ROLE_USER'])
            ->setFirstName('Monika')
            ->setLastName('Monikaite')
            ->setAvailableDays(15)
            ->setPhoneNumber('862556723');
        $manager->persist($user4);
        $this->addReference('user4', $user4);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TagsFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['dataset'];
    }
}
