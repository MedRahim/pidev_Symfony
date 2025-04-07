<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Villes
 *
 * @ORM\Table(name="villes")
 * @ORM\Entity
 */
class Villes
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=100, nullable=false)
     */
    private $nom;

    /**
     * @var string|null
     *
     * @ORM\Column(name="histoire", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $histoire = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="anecdotes", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $anecdotes = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="activites", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $activites = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="gastronomie", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $gastronomie = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="nature", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $nature = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="histoire_interactive", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $histoireInteractive = 'NULL';

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
