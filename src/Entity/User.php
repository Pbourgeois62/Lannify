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
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

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
     * @var Collection<int, Need>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: NeedContribution::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $needContributions;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nickname = null;

    /**
     * @var Collection<int, ParticipantGame>
     */
    #[ORM\OneToMany(targetEntity: ParticipantGame::class, mappedBy: 'participant')]
    private Collection $participantGames;


    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->participantGames = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    // public function __serialize(): array
    // {
    //     $data = (array) $this;
    //     $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

    //     return $data;
    // }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
        ]);
    }

    public function unserialize($serialized)
    {
        [$this->id, $this->email, $this->password,] = unserialize($serialized);
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
            // set the owning side to null (unless already changed)
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
            // set the owning side to null (unless already changed)
            if ($participantGame->getParticipant() === $this) {
                $participantGame->setParticipant(null);
            }
        }

        return $this;
    }
}
