<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\Column(type: "string")]
    protected string $id;

    #[ORM\Column(type: "string", unique: true)]
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Email must contain at least {{ limit }} characters',
        maxMessage: 'Email must not exceed {{ limit }} characters',
        groups: ['create']
    )]
    protected string $email;

    #[ORM\Column(type: "json")]
    protected array $roles = [];

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(groups: ['update'])]
    protected string $firstName = '';

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(groups: ['update'])]
    protected string $lastName = '';

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(groups: ['update'])]
    protected string $phoneNumber = '';

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: "string")]
    #[Assert\NotBlank()]
    private ?string $password = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        foreach ($this->roles as $userRole) {
            if ($userRole === $role) {
                return true;
            }
        }
        return false;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName($lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
