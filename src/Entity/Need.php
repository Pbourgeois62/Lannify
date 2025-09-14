<?php

namespace App\Entity;

use App\Repository\NeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NeedRepository::class)]
class Need
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private int $quantity = 1; // nombre total demandé

    #[ORM\ManyToOne(inversedBy: 'needs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne]
    private ?User $createdBy = null;

    /**
     * @var Collection<int, NeedContribution>
     */
    #[ORM\OneToMany(mappedBy: 'need', targetEntity: NeedContribution::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $contributions;

    public function __construct()
    {
        $this->contributions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return Collection<int, NeedContribution>
     */
    public function getContributions(): Collection
    {
        return $this->contributions;
    }

    public function addContribution(NeedContribution $contribution): static
    {
        if (!$this->contributions->contains($contribution)) {
            $this->contributions->add($contribution);
            $contribution->setNeed($this);
        }
        return $this;
    }

    public function removeContribution(NeedContribution $contribution): static
    {
        if ($this->contributions->removeElement($contribution)) {
            if ($contribution->getNeed() === $this) {
                $contribution->setNeed(null);
            }
        }
        return $this;
    }

    public function getCoveredQuantity(): int
    {
        return array_sum(
            $this->contributions->map(fn(NeedContribution $c) => $c->getQuantity())->toArray()
        );
    }

    public function isFullyCovered(): bool
    {
        return $this->getCoveredQuantity() >= $this->quantity;
    }

    public function getRemainingQuantity(): int
    {
        return ($this->quantity - $this->getCoveredQuantity());
    }

    public function getRemainingQuantityNotFromUser(User $user): int
{
    $totalCovered = $this->getCoveredQuantity();
    $userContribution = array_sum(
        $this->contributions
             ->filter(fn(NeedContribution $c) => $c->getUser() === $user)
             ->map(fn(NeedContribution $c) => $c->getQuantity())
             ->toArray()
    );

    // La quantité restante pour l'utilisateur = tout ce qui reste + ce qu'il avait déjà apporté
    return max(0, ($this->quantity - $totalCovered + $userContribution));
}
}
