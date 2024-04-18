<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    protected string $name;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    // #[Assert\Regex(
    //     pattern: '/^#[a-fA-F0-9]{6}$/',
    //     message: 'Color code must be a valid hexadecimal color code, e.g., #FFFFFF.',
    //     groups: ['update']
    //     // jeigu ne nullas, validuoja regex, jeigu nullas - ne. Reikes custom validationo
    // )]
    protected ?string $colorCode = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getColorCode(): ?string
    {
        return $this->colorCode;
    }

    public function setColorCode(?string $colorCode): static
    {
        $this->colorCode = $colorCode;

        return $this;
    }
}
