<?php

namespace App\Entity;

use App\Repository\BenevoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BenevoleRepository::class)]
class Benevole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s\-]+$/u',
        message: 'Le nom ne peut contenir que des lettres, espaces et tirets.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(
        message: 'L\'email {{ value }} n\'est pas valide.'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $email = null;

    #[Assert\NotBlank(message: 'Le téléphone est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^(\+216)?[2459]\d{7}$/',
        message: 'Le numéro de téléphone n\'est pas valide. Format attendu: +216XXXXXXXX ou XXXXXXXX'
    )]
    #[Assert\Length(
        min: 8,
        max: 20,
        minMessage: 'Le téléphone doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\ManyToMany(targetEntity: Activite::class, inversedBy: 'benevoles')]
    private Collection $activites;

    public function __construct()
    {
        $this->activites = new ArrayCollection();
    }

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * @return Collection<int, Activite>
     */
    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activite $activite): static
    {
        if (!$this->activites->contains($activite)) {
            $this->activites->add($activite);
        }

        return $this;
    }

    public function removeActivite(Activite $activite): static
    {
        $this->activites->removeElement($activite);

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
