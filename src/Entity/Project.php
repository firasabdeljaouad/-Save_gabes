<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du projet est obligatoire.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/',
        message: 'Ce champ ne doit contenir que des lettres.'
    )]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le nom du projet doit contenir au moins 5 caractères.",
        maxMessage: "Le nom du projet ne peut pas dépasser 255 caractères."
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le description est obligatoire.")]

    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le montant cible est obligatoire.")]
    #[Assert\Positive(message: "Le montant cible doit être un nombre positif.")]
    #[Assert\Type(
        type: 'integer',
        message: 'This value must be a number'
    )]
    private ?string $TargetAmount = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Assert\GreaterThan(
        propertyPath: 'startDate',
        message: 'End date must be after start date'
    )]
    private ?\DateTime $endDate = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    #[Assert\Length(
        max: 50,
        maxMessage: "Le statut ne peut pas dépasser 50 caractères."
    )]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description complète est obligatoire.")]
    #[Assert\Length(
        min: 20,
        minMessage: "La description complète doit contenir au moins 20 caractères."
    )]
    private ?string $alldescription = null;
    /**
     * @var Collection<int, Donation>
     */
    #[ORM\OneToMany(targetEntity: Donation::class, mappedBy: 'project')]
    private Collection $donations;

    #[ORM\Column(length: 255,nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom de l'image ne peut pas dépasser 255 caractères."
    )]
    private ?string $image = null;



    public function __construct()
    {
        $this->donations = new ArrayCollection();
    }
    public function __toString(): string
    {
        return (string) $this->id;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTargetAmount(): ?string
    {
        return $this->TargetAmount;
    }

    public function setTargetAmount(?string $TargetAmount): static
    {
        $this->TargetAmount = $TargetAmount;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Donation>
     */
    public function getDonations(): Collection
    {
        return $this->donations;
    }

    public function addDonation(Donation $donation): static
    {
        if (!$this->donations->contains($donation)) {
            $this->donations->add($donation);
            $donation->setProject($this);
        }

        return $this;
    }

    public function removeDonation(Donation $donation): static
    {
        if ($this->donations->removeElement($donation)) {
            // set the owning side to null (unless already changed)
            if ($donation->getProject() === $this) {
                $donation->setProject(null);
            }
        }

        return $this;
    }

    public function getAlldescription(): ?string
    {
        return $this->alldescription;
    }

    public function setAlldescription(string $alldescription): static
    {
        $this->alldescription = $alldescription;

        return $this;
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
