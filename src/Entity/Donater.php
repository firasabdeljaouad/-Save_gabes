<?php

namespace App\Entity;

use App\Repository\DonaterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonaterRepository::class)]
class Donater
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $address = null;

    #[ORM\Column()]
    private ?\DateTime $registrationDate = null;

    #[ORM\ManyToOne(inversedBy: 'donaters')]
    private ?Donation $donations = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTime
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(?\DateTime $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function getDonations(): ?Donation
    {
        return $this->donations;
    }

    public function setDonations(?Donation $donations): static
    {
        $this->donations = $donations;

        return $this;
    }
}
