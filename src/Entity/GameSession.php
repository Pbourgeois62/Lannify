<?php

namespace App\Entity;

use App\Repository\GameSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameSessionRepository::class)]
class GameSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $estimatedDuration = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'gameSessions')]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'gameSessionsOrganized')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'gameSession', orphanRemoval: true)]
    private Collection $messages;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'gameSessions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Game $game = null;

    #[ORM\Column(length: 64, unique: true, nullable: true)]
    private ?string $magicToken = null;

     #[ORM\Column(length: 64, unique: true, nullable: true)]
    private ?string $privateCode = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPrivate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $maxParticipants = null;    

    #[ORM\Column(nullable: true)]
    private ?int $rawgId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImageUrl = null;

    #[ORM\Column(nullable: true)]
    private ?int $currentStep = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->currentStep = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(?int $estimatedDuration): self
    {
        $this->estimatedDuration = $estimatedDuration;
        return $this;
    }


    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setGameSession($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getGameSession() === $this) {
                $message->setGameSession(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getMagicToken(): ?string
    {
        return $this->magicToken;
    }

    public function setMagicToken(string $magicToken): static
    {
        $this->magicToken = $magicToken;
        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): static
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getMaxParticipants(): ?string
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(string $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;

        return $this;
    }

    public function countParticipants(): int
    {
        return $this->participants->count();
    }

    /**
     * Get the value of rawgId
     */
    public function getRawgId()
    {
        return $this->rawgId;
    }

    /**
     * Set the value of rawgId
     *
     * @return  self
     */
    public function setRawgId($rawgId)
    {
        $this->rawgId = $rawgId;

        return $this;
    }

    public function getPrivateCode(): ?string
    {
        return $this->privateCode;
    }

    public function setPrivateCode(string $privateCode): static
    {
        $this->privateCode = $privateCode;

        return $this;
    }

    public function getCoverImageUrl(): ?string
    {
        return $this->coverImageUrl;
    }

    public function setCoverImageUrl(?string $coverImageUrl): static
    {
        $this->coverImageUrl = $coverImageUrl;

        return $this;
    }

    public function getCurrentStep(): ?int
    {
        return $this->currentStep;
    }

    public function setCurrentStep(int $currentStep): static
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    // src/Entity/GameSession.php

public function getStepStatus(int $stepNumber): string
{
    // $stepNumber commence à 1 pour la première étape
    if ($this->currentStep > $stepNumber) {
        return 'completed'; // étape déjà validée
    } elseif ($this->currentStep === $stepNumber) {
        return 'active'; // étape en cours
    } else {
        return 'upcoming'; // étape à venir
    }
}

}
