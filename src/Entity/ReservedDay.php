<?php

namespace App\Entity;

use App\Repository\ReservedDayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
