<?php

namespace App\Entity;

use App\Repository\DonationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonationRepository::class)]
class Donation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Amount est obligatoire.")]
    private ?string $amount = null;

    #[Assert\NotBlank(message: "le method de payment est obligatoire.")]
    #[ORM\Column(length: 50)]
    private ?string $paymentMethod = null;
    #[Assert\NotBlank(message: "Status est obligatoire.")]
    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[Assert\NotBlank(message: "transaction est obligatoire.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column]
    private ?bool $isAnonymous = null;

    #[ORM\ManyToOne(inversedBy: 'donations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    /**
     * @var Collection<int, Donater>
     */
    #[ORM\OneToMany(targetEntity: Donater::class, mappedBy: 'donations')]
    private Collection $donaters;

    public function __construct()
    {
        $this->donaters = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

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

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): static
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function isAnonymous(): ?bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): static
    {
        $this->isAnonymous = $isAnonymous;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection<int, Donater>
     */
    public function getDonaters(): Collection
    {
        return $this->donaters;
    }

    public function addDonater(Donater $donater): static
    {
        if (!$this->donaters->contains($donater)) {
            $this->donaters->add($donater);
            $donater->setDonations($this);
        }

        return $this;
    }

    public function removeDonater(Donater $donater): static
    {
        if ($this->donaters->removeElement($donater)) {
            // set the owning side to null (unless already changed)
            if ($donater->getDonations() === $this) {
                $donater->setDonations(null);
            }
        }

        return $this;
    }
}
