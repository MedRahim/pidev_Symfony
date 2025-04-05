<?php

namespace App\Entity\Ines;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idMedecin", type: "integer")]
    private int $idMedecin;

    #[ORM\Column(name: "nomM", type: "string", length: 255)]
    private string $nomM;

    #[ORM\Column(name: "prenomM", type: "string", length: 255)]
    private string $prenomM;

    #[ORM\Column(name: "specialite", type: "string", length: 255)]
    private string $specialite;

    #[ORM\Column(name: "contact", type: "integer")]
    private int $contact;

    #[ORM\ManyToOne(targetEntity: ServiceHospitalier::class, inversedBy: "medecins")]
    #[ORM\JoinColumn(name: "idService", referencedColumnName: "idService", nullable: false)]
    private ?ServiceHospitalier $service = null;

    // --- Getters et Setters ---
    
    public function getIdMedecin(): int
    {
        return $this->idMedecin;
    }

    public function getNomM(): string
    {
        return $this->nomM;
    }

    public function setNomM(string $nomM): void
    {
        $this->nomM = $nomM;
    }

    public function getPrenomM(): string
    {
        return $this->prenomM;
    }

    public function setPrenomM(string $prenomM): void
    {
        $this->prenomM = $prenomM;
    }

    public function getSpecialite(): string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): void
    {
        $this->specialite = $specialite;
    }

    public function getContact(): int
    {
        return $this->contact;
    }

    public function setContact(int $contact): void
    {
        $this->contact = $contact;
    }

    public function getService(): ?ServiceHospitalier
    {
        return $this->service;
    }

    public function setService(?ServiceHospitalier $service): void
    {
        $this->service = $service;
    }
}
