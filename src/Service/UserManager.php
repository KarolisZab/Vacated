<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Exception\ValidationFailureException;
use App\Trait\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    use LoggerTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {
    }

    public function createUser(UserDTO $userDTO): ?User
    {
        try {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $userDTO->email
            ]);
            if ($existingUser !== null) {
                throw new \Exception('User with this email already exists.');
            }

            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy([
                'username' => $userDTO->username
            ]);
            if ($existingUser !== null) {
                throw new \Exception('User with this username already exists.');
            }

            $user = new User();
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $userDTO->password
            );
            $user->setUsername($userDTO->username)
                ->setEmail($userDTO->email)
                ->setPassword($hashedPassword)
                ->setRoles(['ROLE_USER'])
                ->setFirstName($userDTO->firstName)
                ->setLastName($userDTO->lastName)
                ->setPhoneNumber($userDTO->phoneNumber);

            $errors = $this->validator->validate($user, null, ['create']);
            ValidationFailureException::throwException($errors);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->logger
                ->info("User with username {$userDTO->username} and email {$userDTO->email} created successfully.");

            return $user;
        } catch (\Exception $e) {
            $this->logger->critical('Exception occured while creating user: ' . $e->getMessage());
            return null;
        }
    }

    public function createAdmin(string $username, string $email, string $password): ?User
    {
        try {
            $existingAdmin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingAdmin !== null) {
                throw new \Exception('Admin with this email already exists.');
            }

            $existingAdmin = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existingAdmin !== null) {
                throw new \Exception('Admin with this username already exists.');
            }

            $admin = new User();
            $hashedPassword = $this->passwordHasher->hashPassword(
                $admin,
                $password
            );
            $admin->setUsername($username)
                ->setEmail($email)
                ->setPassword($hashedPassword)
                ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

            $errors = $this->validator->validate($admin, null, ['create']);
            ValidationFailureException::throwException($errors);

            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            $this->logger->info("Admin with username $username and email $email created successfully.");

            return $admin;
        } catch (\Exception $e) {
            $this->logger->critical('Exception occured while creating admin user: ' . $e->getMessage());
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

        $this->logger->info("User with ID $id has been deleted.");

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

        $this->logger->info("Admin $email has been deleted.");

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

    public function getUserByEmail(string $email): ?User
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            return null;
        }

        return $user;
    }

    /**
     * Updates a user with given ID and parameters.
     *
     * @param  string       $id         The ID of an user to update.
     * @param  UserDTO      $userDTO    The data transfer object containing updated user data.
     * @return User|null
     *
     * @throws ValidationFailureException Invalid or missing parameters provided or validation fails.
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
        ValidationFailureException::throwException($errors);

        $this->entityManager->flush();

        $this->logger->info("User with ID $id has been updated.");

        return $user;

        // TODO: Adminas updatint visus userius ir save, bet ne kitus adminus.
    }
}
