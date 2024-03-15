<?php

namespace App\Entity;

use App\Repository\VacationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VacationRepository::class)]
#[ORM\Table(name: 'vacation')]
class Vacation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\Column(type: "string")]
    protected string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Assert\NotBlank(groups: ['create'])]
    protected User $requestedBy;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Assert\NotBlank(groups: ['confirm', 'reject'])]
    protected ?User $reviewedBy = null;

    #[ORM\Column(type: "datetime_immutable")]
    #[Assert\NotBlank(groups:['create'])]
    protected \DateTimeImmutable $requestedAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Assert\NotBlank(groups: ['confirm', 'reject'])]
    protected ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\Column(type: "boolean")]
    protected bool $isConfirmed = false;

    #[ORM\Column(type: "boolean")]
    protected bool $isRejected = false;

    #[ORM\Column(type: "datetime_immutable")]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    protected \DateTimeImmutable $dateFrom;

    #[ORM\Column(type: "datetime_immutable")]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    protected \DateTimeImmutable $dateTo;

    #[ORM\Column(type: "string")]
    #[Assert\Length(max: 255)]
    protected string $note = '';

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(groups: ['reject'])]
    #[Assert\Length(max: 255)]
    protected string $rejectionNote = '';

    public function __construct()
    {
        $this->requestedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRequestedBy(): User
    {
        return $this->requestedBy;
    }

    public function setRequestedBy(User $requestedBy): static
    {
        $this->requestedBy = $requestedBy;

        return $this;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;

        return $this;
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable | \DateTime $requestedAt): static
    {
        if ($requestedAt instanceof \DateTime) {
            $this->requestedAt = \DateTimeImmutable::createFromMutable($requestedAt);
        } else {
            $this->requestedAt = $requestedAt;
        }

        return $this;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(\DateTimeImmutable | \DateTime $reviewedAt): static
    {
        if ($reviewedAt instanceof \DateTime) {
            $this->reviewedAt = \DateTimeImmutable::createFromMutable($reviewedAt);
        } else {
            $this->reviewedAt = $reviewedAt;
        }

        return $this;
    }

    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function setConfirmed(bool $isConfirmed): static
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    public function isRejected(): bool
    {
        return $this->isRejected;
    }

    public function setRejected(bool $isRejected): static
    {
        $this->isRejected = $isRejected;

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

    public function getRejectionNote(): string
    {
        return $this->rejectionNote;
    }

    public function setRejectionNote(string $rejectionNote): static
    {
        $this->rejectionNote = $rejectionNote;

        return $this;
    }
}
