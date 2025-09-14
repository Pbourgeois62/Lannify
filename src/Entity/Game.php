<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(nullable: true)]
    private ?bool $free = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $source = null;

    #[ORM\ManyToOne(inversedBy: 'games')]
    private ?Event $event = null;

    /**
     * @var Collection<int, ParticipantGame>
     */
    #[ORM\OneToMany(targetEntity: ParticipantGame::class, mappedBy: 'game')]
    private Collection $participantGames;

    public function __construct()
    {
        $this->participantGames = new ArrayCollection();
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

    public function isFree(): ?bool
    {
        return $this->free;
    }

    public function setFree(?bool $free): static
    {
        $this->free = $free;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;

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

    /**
     * @return Collection<int, ParticipantGame>
     */
    public function getParticipantGames(): Collection
    {
        return $this->participantGames;
    }

    public function addParticipantGame(ParticipantGame $participantGame): static
    {
        if (!$this->participantGames->contains($participantGame)) {
            $this->participantGames->add($participantGame);
            $participantGame->setGame($this);
        }

        return $this;
    }

    public function removeParticipantGame(ParticipantGame $participantGame): static
    {
        if ($this->participantGames->removeElement($participantGame)) {
            // set the owning side to null (unless already changed)
            if ($participantGame->getGame() === $this) {
                $participantGame->setGame(null);
            }
        }

        return $this;
    }
}
