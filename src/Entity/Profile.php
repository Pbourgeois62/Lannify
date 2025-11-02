<?php

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
#[Vich\Uploadable]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'profile', cascade: ['persist', 'remove'])]
    private ?User $owner = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nickname = null;

    #[ORM\OneToOne(targetEntity: Image::class, mappedBy: 'profile', cascade: ['persist', 'remove'])]
    private ?Image $avatar = null;

    #[ORM\Column(nullable: true)]
    private ?string $discordAvatarUrl = null;

    #[ORM\Column(nullable: true)]
    private ?bool $nickNameEdited = null;

    public function __construct()
    {
        $this->nickNameEdited = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
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

    public function getAvatar(): ?Image
    {
        return $this->avatar;
    }

    public function setAvatar(?Image $avatar): self
    {
        $this->avatar = $avatar;

        if ($avatar && $avatar->getProfile() !== $this) {
            $avatar->setProfile($this);
        }

        return $this;
    }

    public function getDiscordAvatarUrl(): ?string
    {
        return $this->discordAvatarUrl;
    }

    public function getAvatarPath(): string
    {

         if ($this->avatar) {
            return '/uploads/events/' . $this->avatar->getImageName();
        }
        
        if ($this->discordAvatarUrl) {
            return $this->discordAvatarUrl;
        }       

        return '/images/default-avatar.webp';
    }

    public function setAvatarUrl(?string $discordAvatarUrl): self
    {
        $this->discordAvatarUrl = $discordAvatarUrl;
        return $this;
    }

    public function isNickNameEdited(): ?bool
    {
        return $this->nickNameEdited;
    }

    public function setNickNameEdited(bool $nickNameEdited): static
    {
        $this->nickNameEdited = $nickNameEdited;

        return $this;
    }
}
