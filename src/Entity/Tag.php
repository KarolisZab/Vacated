<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    #[Assert\NotBlank()]
    protected string $name;

    #[ORM\Column(type: 'string', length: 7)]
    #[Assert\NotBlank()]
    #[Assert\Regex(
        pattern: '/^#[a-fA-F0-9]{6}$/',
        message: 'Color code must be a valid hexadecimal color code, e.g., #RRGGBB.'
    )]
    protected string $colorCode;

    #[ORM\ManyToMany(targetEntity: ReservedDay::class, mappedBy: 'tags')]
    protected Collection $reservedDays;

    public function __construct()
    {
        $this->reservedDays = new ArrayCollection();
    }

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

    public function getColorCode(): string
    {
        return $this->colorCode;
    }

    public function setColorCode(string $colorCode): static
    {
        $this->colorCode = $colorCode;

        return $this;
    }

    /**
     * @return Collection|ReservedDay[]
     */
    public function getReservedDays(): Collection
    {
        return $this->reservedDays;
    }

    public function addReservedDay(ReservedDay $reservedDay): static
    {
        if (!$this->reservedDays->contains($reservedDay)) {
            $this->reservedDays[] = $reservedDay;
            $reservedDay->addTag($this);
        }

        return $this;
    }

    public function removeReservedDay(ReservedDay $reservedDay): static
    {
        if ($this->reservedDays->removeElement($reservedDay)) {
            $reservedDay->removeTag($this);
        }

        return $this;
    }
}
