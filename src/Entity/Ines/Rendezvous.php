<?php

namespace App\Entity\Ines;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Repository\Ines\RendezvousRepository;

#[ORM\Entity]
#[ORM\Table(name: "rendezvous")]
class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idRendezVous", type: "integer")]
    private ?int $idRendezVous = null;

    #[ORM\Column(name: "dateRendezVous", type: "date")]
    #[Assert\NotBlank(message: "La date du rendez-vous est obligatoire.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "Format de date invalide.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date ne peut pas être dans le passé.")]
    private ?\DateTimeInterface $dateRendezVous = null;

    #[ORM\Column(name: "timeRendezVous", type: "time")]
    #[Assert\NotBlank(message: "L'heure du rendez-vous est obligatoire.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "Format d'heure invalide.")]
    private ?\DateTimeInterface $timeRendezVous = null;

    #[ORM\Column(name: "lieu", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le lieu est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le lieu doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le lieu ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $lieu = null;

    #[ORM\Column(name: "status", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private ?string $status = null;

    #[ORM\Column(name: "idMedecin", type: "integer")]
    #[Assert\NotBlank(message: "L'identifiant du médecin est obligatoire.")]
    #[Assert\Positive(message: "L'ID du médecin doit être un nombre positif.")]
    private ?int $idMedecin = null;

    // ----------------- GETTERS & SETTERS -----------------

    public function getIdRendezVous(): ?int
    {
        return $this->idRendezVous;
    }

    public function getId(): ?int
    {
        return $this->idRendezVous;
    }

    public function getDateRendezVous(): ?\DateTimeInterface
    {
        return $this->dateRendezVous;
    }

    public function setDateRendezVous(?\DateTimeInterface $date): self
    {
        $this->dateRendezVous = $date;
        return $this;
    }

    public function getTimeRendezVous(): ?\DateTimeInterface
    {
        return $this->timeRendezVous;
    }

    public function setTimeRendezVous(?\DateTimeInterface $time): self
    {
        $this->timeRendezVous = $time;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $value): self
    {
        $this->lieu = $value;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $value): self
    {
        $this->status = $value;
        return $this;
    }

    public function getIdMedecin(): ?int
    {
        return $this->idMedecin;
    }

    public function setIdMedecin(?int $value): self
    {
        $this->idMedecin = $value;
        return $this;
    }

    // ----------------- VALIDATIONS PERSONNALISÉES -----------------

    #[Assert\Callback]
    public function validateTimeRange(ExecutionContextInterface $context): void
    {
        if ($this->timeRendezVous) {
            $hour = (int) $this->timeRendezVous->format('H');
            if ($hour < 8 || $hour >= 18) {
                $context->buildViolation("L'heure du rendez-vous doit être entre 08:00 et 18:00.")
                    ->atPath('timeRendezVous')
                    ->addViolation();
            }
        }
    }

    
}
