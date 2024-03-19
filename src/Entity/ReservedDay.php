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
    protected \DateTimeImmutable $dateFrom;

    #[ORM\Column(type: "datetime_immutable")]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    protected \DateTimeImmutable $dateTo;

    #[ORM\Column(type: "string")]
    #[Assert\Length(max: 255)]
    protected string $note = '';

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

    public function getDateFrom(): \DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function setDateFrom(\DateTimeImmutable | \DateTime $dateFrom): static
    {
        $dateFrom = $dateFrom->setTime(0, 0, 0);

        if ($dateFrom instanceof \DateTimeImmutable) {
            $this->dateFrom = $dateFrom;
        } else {
            $this->dateFrom = \DateTimeImmutable::createFromMutable($dateFrom);
        }

        return $this;
    }

    public function getDateTo(): \DateTimeImmutable
    {
        return $this->dateTo;
    }

    public function setDateTo(\DateTimeImmutable | \DateTime $dateTo): static
    {
        $dateTo = $dateTo->setTime(23, 59, 59);

        if ($dateTo instanceof \DateTimeImmutable) {
            $this->dateTo = $dateTo;
        } else {
            $this->dateTo = \DateTimeImmutable::createFromMutable($dateTo);
        }

        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }
}
