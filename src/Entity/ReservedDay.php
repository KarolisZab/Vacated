<?php

namespace App\Entity;

use App\Repository\ReservedDayRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservedDayRepository::class)]
class ReservedDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\Column(type: "string")]
    protected string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Assert\NotBlank(groups: ['create'])]
    protected User $reservedBy;

    #[ORM\Column(type: "datetime_immutable")]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    protected \DateTimeImmutable $reservedFrom;

    #[ORM\Column(type: "datetime_immutable")]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    protected \DateTimeImmutable $reservedTo;

    #[ORM\Column(type: "string")]
    #[Assert\Length(max: 255)]
    protected string $reservedNote = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function getReservedBy(): User
    {
        return $this->reservedBy;
    }

    public function setReservedBy(User $reservedBy): static
    {
        $this->reservedBy = $reservedBy;

        return $this;
    }

    public function getReservedFrom(): \DateTimeImmutable
    {
        return $this->reservedFrom;
    }

    public function setReservedFrom(\DateTimeImmutable | \DateTime $reservedFrom): static
    {
        $reservedFrom = $reservedFrom->setTime(0, 0, 0);

        if ($reservedFrom instanceof \DateTimeImmutable) {
            $this->reservedFrom = $reservedFrom;
        } else {
            $this->reservedFrom = \DateTimeImmutable::createFromMutable($reservedFrom);
        }

        return $this;
    }

    public function getReservedTo(): \DateTimeImmutable
    {
        return $this->reservedTo;
    }

    public function setReservedTo(\DateTimeImmutable | \DateTime $reservedTo): static
    {
        $reservedTo = $reservedTo->setTime(23, 59, 59);

        if ($reservedTo instanceof \DateTimeImmutable) {
            $this->reservedTo = $reservedTo;
        } else {
            $this->reservedTo = \DateTimeImmutable::createFromMutable($reservedTo);
        }

        return $this;
    }

    public function getReservedNote(): string
    {
        return $this->reservedNote;
    }

    public function setReservedNote(string $reservedNote): static
    {
        $this->reservedNote = $reservedNote;

        return $this;
    }
}
