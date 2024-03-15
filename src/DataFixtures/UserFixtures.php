<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin1 = new User();
        $password = $this->passwordHasher->hashPassword($admin1, 'test');
        $admin1->setEmail("jwttest@test.com")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $manager->persist($admin1);

        $admin2 = new User();
        $password = $this->passwordHasher->hashPassword($admin2, 'test');
        $admin2->setEmail("expiration@test")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $manager->persist($admin2);

        $admin3 = new User();
        $password = $this->passwordHasher->hashPassword($admin3, 'test');
        $admin3->setEmail("apitest@test.com")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $manager->persist($admin3);

        $admin4 = new User();
        $password = $this->passwordHasher->hashPassword($admin4, 'test');
        $admin4->setEmail("vacationtest@test.com")
            ->setPassword($password)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $manager->persist($admin4);

        $user1 = new User();
        $password = $this->passwordHasher->hashPassword($user1, 'test');
        $user1->setEmail("regtest@test.com")
            ->setPassword($password)
            ->setRoles(['ROLE_USER'])
            ->setFirstName('')
            ->setLastName('')
            ->setPhoneNumber('');
        $manager->persist($user1);

        $user2 = new User();
        $password = $this->passwordHasher->hashPassword($user2, 'test');
        $user2->setEmail("userconfirmtest@test.com")
            ->setPassword($password)
            ->setRoles(['ROLE_USER'])
            ->setFirstName('')
            ->setLastName('')
            ->setPhoneNumber('');
        $manager->persist($user2);

        $user3 = new User();
        $password = $this->passwordHasher->hashPassword($user3, 'test');
        $user3->setEmail("rejecttest@test.com")
            ->setPassword($password)
            ->setRoles(['ROLE_USER'])
            ->setFirstName('')
            ->setLastName('')
            ->setPhoneNumber('');
        $manager->persist($user3);

        $manager->flush();

        $this->addReference('vacationtest', $admin4);
        $this->addReference('apitest', $admin3);
    }
}
