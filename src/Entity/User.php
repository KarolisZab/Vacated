<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const MAX_AVAILABLE_DAYS = 20;

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

    #[ORM\Column(type: "boolean")]
    protected bool $isAdmin = false;

    #[ORM\Column(type: "integer")]
    protected int $availableDays = self::MAX_AVAILABLE_DAYS;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: "string")]
    #[Assert\NotBlank()]
    private ?string $password = null;

    #[ORM\ManyToMany(targetEntity: Tag::class)]
    protected Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

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

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
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

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setTags(ArrayCollection $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function getAvailableDays(): int
    {
        return $this->availableDays;
    }

    public function setAvailableDays(int $availbleDays): static
    {
        $this->availableDays = $availbleDays;

        if ($this->availableDays < 0) {
            $this->availableDays = 0;
        }

        if ($this->availableDays > self::MAX_AVAILABLE_DAYS) {
            $this->availableDays = self::MAX_AVAILABLE_DAYS;
        }


        return $this;
    }
}
