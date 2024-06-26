<?php

namespace App\Service;

use App\DTO\TagDTO;
use App\DTO\UserDTO;
use App\Entity\User;
use App\Exception\ValidationFailureException;
use App\Service\Mailer\MailerManagerInterface;
use App\Trait\LoggerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    use LoggerTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private TagManager $tagManager,
        private MailerManagerInterface $mailerManager
    ) {
    }

    public function createUser(UserDTO $userDTO): ?User
    {
        try {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $userDTO->email
            ]);

            if (null !== $existingUser) {
                return null;
            }

            $password = $this->generateRandomPassword();

            $user = new User();
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $password
            );
            $user->setEmail($userDTO->email)
                ->setPassword($hashedPassword)
                ->setRoles(['ROLE_USER'])
                ->setFirstName($userDTO->firstName)
                ->setLastName($userDTO->lastName)
                ->setPhoneNumber($userDTO->phoneNumber);

            foreach ($userDTO->tags as $tagDTO) {
                $tag = $this->tagManager->createOrGetTag(new TagDTO($tagDTO['name'], $tagDTO['colorCode']), false);
                $user->addTag($tag);
            }

            $errors = $this->validator->validate($user, null, ['create']);
            ValidationFailureException::throwException($errors);

            $this->mailerManager->sendWelcomeEmail($userDTO->email, $password);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->logger
                ->info("User with email {$userDTO->email} created successfully.");

            return $user;
        } catch (ORMException $e) {
            $this->logger->critical("Exception occured while creating user {$userDTO->email} : " . $e->getMessage());
            throw $e;
        }
    }

    private function generateRandomPassword(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';

        for ($i = 0; $i < 16; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $password .= $characters[$index];
        }

        return $password;
    }

    public function createAdmin(string $email, string $password): ?User
    {
        try {
            $existingAdmin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingAdmin !== null) {
                return null;
            }

            $admin = new User();
            $hashedPassword = $this->passwordHasher->hashPassword(
                $admin,
                $password
            );
            $admin->setEmail($email)
                ->setPassword($hashedPassword)
                ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
                ->setIsAdmin(true);

            $errors = $this->validator->validate($admin, null, ['create']);
            ValidationFailureException::throwException($errors);

            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            $this->logger->info("Admin with email $email created successfully.");

            return $admin;
        } catch (ORMException $e) {
            $this->logger->critical("Exception occured while creating admin user {$email} : " . $e->getMessage());
            throw $e;
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

        return $userRepository->findAll();
    }

    /**
     * @return \App\Entity\User[]
     */
    public function getUsers(int $limit = 10, int $offset = 0, ?string $filter = null): array
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->getUsers($limit, $offset, $filter);
    }

    public function getUser(string $id): ?User
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->find($id);
    }

    public function getUserByEmail(string $email): ?User
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->findOneBy(['email' => $email]);
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

        $addTags = [];
        foreach ($userDTO->tags as $tagDTO) {
            $tag = $this->tagManager->createOrGetTag(new TagDTO($tagDTO['name'], $tagDTO['colorCode']), false);
            $addTags[] = $tag;
        }

        $user->setTags(new ArrayCollection($addTags));

        $this->entityManager->flush();

        $this->logger->info("User with ID $id has been updated.");

        return $user;
    }

    public function getEmployeeCount(): int
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->getEmployeesCount();
    }

    public function getUsersCount(?string $filter = null): int
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->countAllUsers($filter);
    }

    public function getAvailableDaysForUser(string $email): ?int
    {
        $user = $this->getUserByEmail($email);

        if ($user === null) {
            return null;
        }

        $availableDays = $user->getAvailableDays();

        return $availableDays;
    }

    public function changePassword(User $user, string $newPassword)
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();
    }

    public function resetPassword(string $email): bool
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return false;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'test');
        $user->setPassword($hashedPassword);

        try {
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical(
                "Exception occured while requesting password reset for user {$user->getEmail()} : " . $e->getMessage()
            );
            throw $e;
        }
    }
}
