<?php

namespace App\Entity;

use App\Repository\TypeEvenementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeEvenementRepository::class)]
class TypeEvenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $organisateurr = null;

    #[ORM\Column(length: 255)]
    private ?string $partenaires = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $materielNecessaire = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getOrganisateurr(): ?string
    {
        return $this->organisateurr;
    }

    public function setOrganisateurr(string $organisateurr): static
    {
        $this->organisateurr = $organisateurr;

        return $this;
    }

    public function getPartenaires(): ?string
    {
        return $this->partenaires;
    }

    public function setPartenaires(string $partenaires): static
    {
        $this->partenaires = $partenaires;

        return $this;
    }

    public function getMaterielNecessaire(): ?string
    {
        return $this->materielNecessaire;
    }

    public function setMaterielNecessaire(?string $materielNecessaire): static
    {
        $this->materielNecessaire = $materielNecessaire;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
