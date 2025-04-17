<?php

namespace App\Entity\Ines;

use App\Repository\Ines\ServiceHospitalierRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\Ines\Medecin;

#[ORM\Entity(repositoryClass: ServiceHospitalierRepository::class)]
#[ORM\Table(name: "servicehospitalier")]
#[UniqueEntity(fields: ['nomService'], message: "Ce nom de service est déjà utilisé.")]
class ServiceHospitalier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idService", type: "integer")]
    private ?int $idService = null;

    #[ORM\Column(name: "nomService", type: "string", length: 100, unique: true)]
    #[Assert\NotBlank(message: "Le nom du service est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le nom du service doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom du service ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nomService = null;

    #[ORM\Column(name: "description", type: "text")]
    #[Assert\NotBlank(message: "La description du service est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(name: "nombreLitsDisponibles", type: "integer")]
    #[Assert\NotNull(message: "Le nombre de lits est requis.")]
    #[Assert\GreaterThanOrEqual(0, message: "Le nombre de lits disponibles ne peut pas être négatif.")]
    private ?int $nombreLitsDisponibles = null;



    #[ORM\OneToMany(mappedBy: "service", targetEntity: Medecin::class)]
    private Collection $medecins;

    public function __construct()
    {
        $this->medecins = new ArrayCollection();
    }

    public function getIdService(): ?int
    {
        return $this->idService;
    }

    public function getNomService(): ?string
    {
        return $this->nomService;
    }

    public function setNomService(string $nomService): self
    {
        $this->nomService = $nomService;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getNombreLitsDisponibles(): ?int
    {
        return $this->nombreLitsDisponibles;
    }

    public function setNombreLitsDisponibles(int $nombreLitsDisponibles): self
    {
        $this->nombreLitsDisponibles = $nombreLitsDisponibles;
        return $this;
    }

    public function getMedecins(): Collection
    {
        return $this->medecins;
    }
}
