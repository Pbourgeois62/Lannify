<?php

namespace App\Entity;

use Serializable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Serializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(nullable: true)]
    private ?string $discordId = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'users')]
    private Collection $events;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $organizer = null;

    #[ORM\OneToOne(inversedBy: 'owner', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Profile $profile = null;

    /**
     * @var Collection<int, NeedContribution>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: NeedContribution::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $needContributions;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nickname = null;

    /**
     * @var Collection<int, ParticipantGame>
     */
    #[ORM\OneToMany(targetEntity: ParticipantGame::class, mappedBy: 'participant', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $participantGames;

    /**
     * @var Collection<int, EventImage>
     */
    #[ORM\OneToMany(targetEntity: EventImage::class, mappedBy: 'uploadedBy', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $eventImages;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender', orphanRemoval: true)]
    private Collection $messages;

    /**
     * @var Collection<int, Feedback>
     */
    #[ORM\OneToMany(targetEntity: Feedback::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $feedback;

    /**
     * @var Collection<int, FeedbackMessages>
     */
    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: FeedbackMessage::class, orphanRemoval: true)]
    private Collection $feedbackMessages;

    /**
     * @var Collection<int, Commentary>
     */
    #[ORM\OneToMany(targetEntity: Commentary::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $commentaries;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->needContributions = new ArrayCollection();
        $this->participantGames = new ArrayCollection();
        $this->eventImages = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->feedback = new ArrayCollection();
        $this->feedbackMessages = new ArrayCollection();
        $this->commentaries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
        ]);
    }

    public function unserialize($serialized): void
    {
        [$this->id, $this->email, $this->password] = unserialize($serialized);
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->addUser($this);
        }
        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeUser($this);
        }
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

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;
        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;
        if ($profile && $profile->getOwner() !== $this) {
            $profile->setOwner($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, NeedContribution>
     */
    public function getNeedContributions(): Collection
    {
        return $this->needContributions;
    }

    public function addNeedContribution(NeedContribution $needContribution): static
    {
        if (!$this->needContributions->contains($needContribution)) {
            $this->needContributions->add($needContribution);
            $needContribution->setUser($this);
        }
        return $this;
    }

    public function removeNeedContribution(NeedContribution $needContribution): static
    {
        if ($this->needContributions->removeElement($needContribution)) {
            if ($needContribution->getUser() === $this) {
                $needContribution->setUser(null);
            }
        }
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
            $participantGame->setParticipant($this);
        }
        return $this;
    }

    public function removeParticipantGame(ParticipantGame $participantGame): static
    {
        if ($this->participantGames->removeElement($participantGame)) {
            if ($participantGame->getParticipant() === $this) {
                $participantGame->setParticipant(null);
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
            $eventImage->setUploadedBy($this);
        }
        return $this;
    }

    public function removeEventImage(EventImage $eventImage): static
    {
        if ($this->eventImages->removeElement($eventImage)) {
            if ($eventImage->getUploadedBy() === $this) {
                $eventImage->setUploadedBy(null);
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
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Feedback>
     */
    public function getFeedback(): Collection
    {
        return $this->feedback;
    }

    public function addFeedback(Feedback $feedback): static
    {
        if (!$this->feedback->contains($feedback)) {
            $this->feedback->add($feedback);
            $feedback->setAuthor($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): static
    {
        if ($this->feedback->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getAuthor() === $this) {
                $feedback->setAuthor(null);
            }
        }

        return $this;
    }

    public function addFeedbackMessage(FeedbackMessage $message): static
    {
        if (!$this->feedbackMessages->contains($message)) {
            $this->feedbackMessages->add($message);
            $message->setSender($this);
        }
        return $this;
    }

    public function removeFeedbackMessage(FeedbackMessage $message): static
    {
        if ($this->feedbackMessages->removeElement($message)) {
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }
        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    /**
     * @return Collection<int, Commentary>
     */
    public function getCommentaries(): Collection
    {
        return $this->commentaries;
    }

    public function addCommentary(Commentary $commentary): static
    {
        if (!$this->commentaries->contains($commentary)) {
            $this->commentaries->add($commentary);
            $commentary->setAuthor($this);
        }

        return $this;
    }

    public function removeCommentary(Commentary $commentary): static
    {
        if ($this->commentaries->removeElement($commentary)) {
            // set the owning side to null (unless already changed)
            if ($commentary->getAuthor() === $this) {
                $commentary->setAuthor(null);
            }
        }

        return $this;
    }
    
    public function getDiscordId(): ?string
    {
        return $this->discordId;
    }
    public function setDiscordId(?string $discordId): self
    {
        $this->discordId = $discordId;
        return $this;
    }
}
