<?php

namespace App\Entity;

use App\Repository\ParticipantGameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantGameRepository::class)]
class ParticipantGame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participantGames')]
    private ?User $participant = null;

    #[ORM\ManyToOne(inversedBy: 'participantGames')]
    private ?Game $game = null;

    #[ORM\Column(type: 'boolean')]
    private bool $owns = false;

    #[ORM\Column]
    private ?bool $interested = null;

    public function __construct()
    {
        $this->owns = false;
        $this->interested = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(?User $participant): static
    {
        $this->participant = $participant;

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

    public function getOwns(): ?bool
    {
        return $this->owns;
    }

    public function setOwns(bool $owns): static
    {
        $this->owns = $owns;
        return $this;
    }


    public function getInterested(): ?bool
    {
        return $this->interested;
    }

    public function setInterested(bool $interested): static
    {
        $this->interested = $interested;

        return $this;
    }

    public function isReadyToPlay(): bool
    {
        return $this->owns && $this->interested;
    }
}
