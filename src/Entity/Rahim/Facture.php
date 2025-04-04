<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFacture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateLimitePaiement = null;

    #[ORM\Column]
    private ?float $prixFact = null;

    #[ORM\Column(length: 255)]
    private ?string $typeFacture = null;

    #[ORM\Column]
    private ?bool $state = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePaiement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(\DateTimeInterface $dateFacture): static
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }

    public function getDateLimitePaiement(): ?\DateTimeInterface
    {
        return $this->dateLimitePaiement;
    }

    public function setDateLimitePaiement(\DateTimeInterface $dateLimitePaiement): static
    {
        $this->dateLimitePaiement = $dateLimitePaiement;

        return $this;
    }

    public function getPrixFact(): ?float
    {
        return $this->prixFact;
    }

    public function setPrixFact(float $prixFact): static
    {
        $this->prixFact = $prixFact;

        return $this;
    }

    public function getTypeFacture(): ?string
    {
        return $this->typeFacture;
    }

    public function setTypeFacture(string $typeFacture): static
    {
        $this->typeFacture = $typeFacture;

        return $this;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }
}
