<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
    }

    public function createAdmin(string $username, string $email, string $password): ?User
    {
        try {
            $admin = new User();
            $hashedPassword = $this->passwordHasher->hashPassword(
                $admin,
                $password
            );
            $admin->setUsername($username)
                ->setEmail($email)
                ->setPassword($hashedPassword)
                ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

            $errors = $this->validator->validate($admin);

            if (count($errors) > 0) {
                $validationErrors = [];
                foreach ($errors as $error) {
                    $validationErrors[$error->getPropertyPath()] = $error->getMessage();
                }
                throw new \Exception(json_encode($validationErrors), 400);
            }

            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            return $admin;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function deleteUser(string $id): bool
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if ($user === null) {
            return false;
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            throw new \Exception('Admin users cannot be deleted', 403);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }

    public function deleteAdmin(string $email): bool
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            return false;
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @return \App\Entity\User[]
     */
    public function getAllUsers(): array
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $allUsers = $userRepository->findAll();

        return $allUsers;
    }

    public function getUser(string $id): ?User
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if ($user === null) {
            return null;
        }

        return $user;
    }

    /**
     * updateUser - Updates a user with given ID and parameters.
     *
     * @param  string $id - The ID of an user to update.
     * @param  UserDTO $userDTO - The data transfer object containing updated user data.
     * @return User|null - Updated user object.
     *
     * @throws \Exception - Invalid or missing parameters provided or validation fails.
     */
    public function updateUser(string $id, UserDTO $userDTO): ?User
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if ($user === null) {
            return null;
        }

        $user->setFirstName($userDTO->firstName)
            ->setLastName($userDTO->lastName)
            ->setPhoneNumber($userDTO->phoneNumber);

        $errors = $this->validator->validate($user, null, ['update']);

        if (count($errors) > 0) {
            $validationErrors = [];
            foreach ($errors as $error) {
                $validationErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \Exception(json_encode($validationErrors), 400);
        }

        $this->entityManager->flush();

        return $user;

        // TODO: Adminas updatint visus userius ir save, bet ne kitus adminus.
    }
}
