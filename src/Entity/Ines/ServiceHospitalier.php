<?php

namespace App\Entity\Ines;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;



#[ORM\Entity]
#[ORM\Table(name: "servicehospitalier")]
class ServiceHospitalier
{

    #[ORM\Id]
    #[ORM\GeneratedValue] // 🔥 Ajout de GeneratedValue pour l'auto-incrémentation
    #[ORM\Column(name: "idService", type: "integer")] // 🔥 Spécifie le bon nom de colonne
    private int $idService;

    #[ORM\Column(name: "nomService", type: "string", length: 100)] // 🔥 Ajout du bon nom de colonne
    private string $nomService;

    #[ORM\Column(name: "description", type: "text")]
    private string $description;

    #[ORM\Column(name: "nombreLitsDisponibles", type: "integer")]
    private int $nombreLitsDisponibles;

    #[ORM\OneToMany(mappedBy: "service", targetEntity: Medecin::class)]
    private Collection $medecins;

    public function __construct()
    {
        $this->medecins = new ArrayCollection();
    }

    public function getIdService()
    {
        return $this->idService;
    }

    public function setIdService($value)
    {
        $this->idService = $value;
    }

    public function getNomService()
    {
        return $this->nomService;
    }

    public function setNomService($value)
    {
        $this->nomService = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getNombreLitsDisponibles()
    {
        return $this->nombreLitsDisponibles;
    }

    public function setNombreLitsDisponibles($value)
    {
        $this->nombreLitsDisponibles = $value;
    }

    public function getMedecins(): Collection
    {
        return $this->medecins;
    }
}
