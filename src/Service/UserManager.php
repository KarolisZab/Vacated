<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
    }

    // public function createUser(string $username, string $email,
    // string $password, string $firstName, string $lastName)
    // {
    //     $user = new User();
    //     $hashedPassword = $this->passwordHasher->hashPassword(
    //         $user,
    //         $password
    //     );
    //     $user->setUsername($username)
    //         ->setEmail($email)
    //         ->setPassword($hashedPassword)
    //         ->setFirstName($firstName)
    //         ->setLastName($lastName)
    //         ->setRoles(['ROLE_USER']);

    //     $this->entityManager->persist($user);
    //     $this->entityManager->flush();

    //     return 0;
    // }

    public function createAdmin(string $username, string $email, string $password)
    {
        $admin = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $password
        );
        $admin->setUsername($username)
            ->setEmail($email)
            ->setPassword($hashedPassword)
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        return 0;
    }

    public function deleteUser(string $id)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if ($user === null) {
            // return null;
            throw new UserNotFoundException('User not found!', 404);
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            throw new \Exception('Admin users cannot be deleted', 403);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $user;
    }

    public function deleteAdmin(string $identifier)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        // $user = $userRepository->find($id);

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = $userRepository->findOneBy(['email' => $identifier]);
        } else {
            $user = $userRepository->findOneBy(['username' => $identifier]);
        }

        if ($user === null) {
            // return null;
            throw new UserNotFoundException('User not found!', 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getAllUsers()
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $allUsers = $userRepository->findAll();

        return $allUsers;
    }

    public function getUser(string $id)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if ($user === null) {
            throw new UserNotFoundException('User not found', 404);
        }

        return $user;
    }

    public function updateUser(string $id, array $parameters)
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if ($user === null) {
            throw new UserNotFoundException('User not found', 404);
        }

        if (empty($parameters['first_name']) || empty($parameters['last_name']) || empty($parameters['phone_number'])) {
            throw new \Exception('Invalid or missing parameters', 400);
        }

        $user->setFirstName($parameters['first_name'])
            ->setLastName($parameters['last_name'])
            ->setPhoneNumber($parameters['phone_number']);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            // Handle validation errors, for example, return a 400 Bad Request response
            $validationErrors = [];
            foreach ($errors as $error) {
                $validationErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \Exception(json_encode($validationErrors), 400);
            // return new JsonResponse($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->flush();

        return $user;

        // TODO: Adminas updatint visus userius ir save, bet ne kitus adminus.
    }
}
