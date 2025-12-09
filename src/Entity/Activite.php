<?php

namespace App\Entity;

use App\Repository\ActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActiviteRepository::class)]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le titre doit contenir au moins 3 caractères.',
        maxMessage: 'Le titre ne peut pas dépasser 100 caractères.'
    )]
    #[ORM\Column(length: 255)]
    private ?string $title = null;
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description doit contenir au moins 10 caractères.'
    )]
    #[ORM\Column(type: Types::TEXT)]

    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'La date est obligatoire.')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Assert\GreaterThanOrEqual(
        'today',
        message: 'La date doit être aujourd\'hui ou dans le futur.'
    )]

    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le lieu est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le lieu doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le lieu ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $lieu = null;

    #[ORM\ManyToMany(targetEntity: Benevole::class, mappedBy: 'activites')]
    private Collection $benevoles;
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom de l\'image ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[ORM\Column(length: 255)]
    private ?string $image = null;

    public function __construct()
    {
        $this->benevoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;
        return $this;
    }

    /**
     * @return Collection<int, Benevole>
     */
    public function getBenevoles(): Collection
    {
        return $this->benevoles;
    }

    public function addBenevole(Benevole $benevole): static
    {
        if (!$this->benevoles->contains($benevole)) {
            $this->benevoles->add($benevole);
            $benevole->addActivite($this);
        }

        return $this;
    }

    public function removeBenevole(Benevole $benevole): static
    {
        if ($this->benevoles->removeElement($benevole)) {
            $benevole->removeActivite($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }


}

