<?php

namespace App\Entity\Ines;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Rendezvous
{

    #[ORM\Id]
    #[ORM\Column(name: "idRendezVous", type: "integer")]
    #[ORM\GeneratedValue] 
    private int $idRendezVous;

    #[ORM\Column(name: "dateRendezVous",type: "date")]
    private \DateTimeInterface $dateRendezVous;

    #[ORM\Column(name: "timeRendezVous",type: 'time')] 
    private \DateTimeInterface $timeRendezVous;

    #[ORM\Column(name: "lieu",type: "string", length: 255)]
    private string $lieu;

    #[ORM\Column(name: "status",type: "string", length: 255)]
    private string $status;

    #[ORM\Column(name: "idMedecin",type: "integer")]
    private int $idMedecin;

    public function getIdRendezVous()
    {
        return $this->idRendezVous;
    }

    public function setIdRendezVous($value)
    {
        $this->idRendezVous = $value;
    }

    public function getDateRendezVous(): \DateTimeInterface
    {
        return $this->dateRendezVous;
    }

    public function setDateRendezVous(\DateTimeInterface $date): self
    {
        $this->dateRendezVous = $date;
        return $this;
    }

    public function getTimeRendezVous(): \DateTimeInterface
    {
        return $this->timeRendezVous;
    }

    public function setTimeRendezVous(\DateTimeInterface $time): self
    {
        $this->timeRendezVous = $time;
        return $this;
    }

    public function getLieu()
    {
        return $this->lieu;
    }

    public function setLieu($value)
    {
        $this->lieu = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getIdMedecin()
    {
        return $this->idMedecin;
    }

    public function setIdMedecin($value)
    {
        $this->idMedecin = $value;
    }
}
