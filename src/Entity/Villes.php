<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'villes')]
class Villes
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(name: 'nom', type: 'string', length: 100, nullable: false)]
    private string $nom;

    #[ORM\Column(name: 'histoire', type: 'text', nullable: true, options: ['default' => null])]
    private ?string $histoire = null;

    #[ORM\Column(name: 'anecdotes', type: 'text', nullable: true, options: ['default' => null])]
    private ?string $anecdotes = null;

    #[ORM\Column(name: 'activites', type: 'text', nullable: true, options: ['default' => null])]
    private ?string $activites = null;

    #[ORM\Column(name: 'gastronomie', type: 'text', nullable: true, options: ['default' => null])]
    private ?string $gastronomie = null;

    #[ORM\Column(name: 'nature', type: 'text', nullable: true, options: ['default' => null])]
    private ?string $nature = null;

    #[ORM\Column(name: 'histoire_interactive', type: 'text', nullable: true, options: ['default' => null])]
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