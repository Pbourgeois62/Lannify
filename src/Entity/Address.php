<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Event;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "La rue est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La rue ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $street;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: "La ville est obligatoire.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "La ville ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $city;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: "Le code postal est obligatoire.")]
    #[Assert\Length(
        max: 20,
        maxMessage: "Le code postal ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: '/^\d{4,10}$/',
        message: "Le code postal doit contenir entre 4 et 10 chiffres."
    )]
    private string $postalCode;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: "Le pays est obligatoire.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Le nom du pays ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $country;    

    #[ORM\OneToOne(mappedBy: 'address', targetEntity: Event::class, cascade: ['persist', 'remove'])]
    private ?Event $event = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }   

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        if ($event && $event->getAddress() !== $this) {
            $event->setAddress($this);
        }

        return $this;
    }
}
