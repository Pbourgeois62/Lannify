<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $endDate = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[ORM\OneToOne(targetEntity: Address::class, inversedBy: 'event', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Address $address = null;

    #[ORM\OneToOne(targetEntity: Image::class, mappedBy: 'event', cascade: ['persist', 'remove'])]
    private ?Image $coverImage = null;

    /**
     * @var Collection<int, Need>
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Need::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $needs;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Game>
     */
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'event', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $games;

    /**
     * @var Collection<int, EventImage>
     */
    #[ORM\OneToMany(targetEntity: EventImage::class, mappedBy: 'event', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $eventImages;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'event', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $messages;

    #[ORM\Column (type: 'boolean', nullable: true)]
    private ?bool $isClosed = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->games = new ArrayCollection();
        $this->needs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->eventImages = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->isClosed = false;
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

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);
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

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): self
    {
        $this->organizer = $organizer;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;
        if ($address && $address->getEvent() !== $this) {
            $address->setEvent($this);
        }
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCoverImage(): ?Image
    {
        return $this->coverImage;
    }

    public function setCoverImage(?Image $coverImage): static
    {
        $this->coverImage = $coverImage;
        return $this;
    }

    public function getNeeds(): Collection
    {
        return $this->needs;
    }

    public function addNeed(Need $need): static
    {
        if (!$this->needs->contains($need)) {
            $this->needs->add($need);
            $need->setEvent($this);
        }
        return $this;
    }

    public function removeNeed(Need $need): static
    {
        if ($this->needs->removeElement($need)) {
            if ($need->getEvent() === $this) {
                $need->setEvent(null);
            }
        }
        return $this;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->setEvent($this);
        }
        return $this;
    }

    public function removeGame(Game $game): static
    {
        if ($this->games->removeElement($game)) {
            if ($game->getEvent() === $this) {
                $game->setEvent(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, EventImage>
     */
    public function getEventImages(): Collection
    {
        return $this->eventImages;
    }

    public function addEventImage(EventImage $eventImage): static
    {
        if (!$this->eventImages->contains($eventImage)) {
            $this->eventImages->add($eventImage);
            $eventImage->setEvent($this);
        }

        return $this;
    }

    public function removeEventImage(EventImage $eventImage): static
    {
        if ($this->eventImages->removeElement($eventImage)) {
            // set the owning side to null (unless already changed)
            if ($eventImage->getEvent() === $this) {
                $eventImage->setEvent(null);
            }
        }
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
            $message->setEvent($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getEvent() === $this) {
                $message->setEvent(null);
            }
        }

        return $this;
    }

    public function isClosed(): ?bool
    {
        return $this->isClosed;
    }

    public function setClosed(bool $isClosed): static
    {
        $this->isClosed = $isClosed;

        return $this;
    }
}
