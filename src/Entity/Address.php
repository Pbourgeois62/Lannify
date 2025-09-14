<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $street;

    #[ORM\Column(type: 'string', length: 100)]
    private string $city;

    #[ORM\Column(type: 'string', length: 20)]
    private string $postalCode;

    #[ORM\Column(type: 'string', length: 100)]
    private string $country;

    // Latitude et longitude pour géolocalisation
    // #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    // private ?float $latitude = null;

    // #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    // private ?float $longitude = null;

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

    // public function getLatitude(): ?float
    // {
    //     return $this->latitude;
    // }

    // public function setLatitude(?float $latitude): self
    // {
    //     $this->latitude = $latitude;
    //     return $this;
    // }

    // public function getLongitude(): ?float
    // {
    //     return $this->longitude;
    // }

    // public function setLongitude(?float $longitude): self
    // {
    //     $this->longitude = $longitude;
    //     return $this;
    // }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        // Synchronisation bidirectionnelle
        if ($event && $event->getAddress() !== $this) {
            $event->setAddress($this);
        }

        return $this;
    }
}
