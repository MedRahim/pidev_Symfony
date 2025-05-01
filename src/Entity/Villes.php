<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'villes')]
#[ORM\Entity]
class Villes
{
    #[ORM\Column(name: 'id', type: Types::INTEGER, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', type: Types::STRING, length: 100, nullable: false)]
    private string $nom;

    #[ORM\Column(
        name: 'histoire', 
        type: Types::TEXT, 
        length: 65535, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $histoire = null;

    #[ORM\Column(
        name: 'anecdotes', 
        type: Types::TEXT, 
        length: 65535, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $anecdotes = null;

    #[ORM\Column(
        name: 'activites', 
        type: Types::TEXT, 
        length: 65535, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $activites = null;

    #[ORM\Column(
        name: 'gastronomie', 
        type: Types::TEXT, 
        length: 65535, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $gastronomie = null;

    #[ORM\Column(
        name: 'nature', 
        type: Types::TEXT, 
        length: 65535, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $nature = null;

    #[ORM\Column(
        name: 'histoire_interactive', 
        type: Types::TEXT, 
        length: 65535, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $histoireInteractive = null;

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

    public function getHistoire(): ?string
    {
        return $this->histoire;
    }

    public function setHistoire(?string $histoire): static
    {
        $this->histoire = $histoire;
        return $this;
    }

    public function getAnecdotes(): ?string
    {
        return $this->anecdotes;
    }

    public function setAnecdotes(?string $anecdotes): static
    {
        $this->anecdotes = $anecdotes;
        return $this;
    }

    public function getActivites(): ?string
    {
        return $this->activites;
    }

    public function setActivites(?string $activites): static
    {
        $this->activites = $activites;
        return $this;
    }

    public function getGastronomie(): ?string
    {
        return $this->gastronomie;
    }

    public function setGastronomie(?string $gastronomie): static
    {
        $this->gastronomie = $gastronomie;
        return $this;
    }

    public function getNature(): ?string
    {
        return $this->nature;
    }

    public function setNature(?string $nature): static
    {
        $this->nature = $nature;
        return $this;
    }

    public function getHistoireInteractive(): ?string
    {
        return $this->histoireInteractive;
    }

    public function setHistoireInteractive(?string $histoireInteractive): static
    {
        $this->histoireInteractive = $histoireInteractive;
        return $this;
    }
}