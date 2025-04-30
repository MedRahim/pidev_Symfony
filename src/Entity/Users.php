<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="email", columns={"email"})})
 * @ORM\Entity
 */
class Users
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
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var int
     *
     * @ORM\Column(name="eco_km", type="integer", options={"default"=0})
     */
    private $ecoKm = 0;

    /**
     * @var array
     *
     * @ORM\Column(name="modes_used", type="json", options={"default"="[]"})
     */
    private $modesUsed = [];

    /**
     * @var int
     *
     * @ORM\Column(name="trips_count", type="integer", options={"default"=0})
     */
    private $tripsCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="co2_saved", type="integer", options={"default"=0})
     */
    private $co2Saved = 0;

    /**
 * @var array
 *
 * @ORM\Column(name="garden", type="json", options={"default"="[]"})
 */
private $garden = [];

    // Getters/Setters existants...

    public function getEcoKm(): int
    {
        return $this->ecoKm;
    }

    public function setEcoKm(int $ecoKm): self
    {
        $this->ecoKm = $ecoKm;
        return $this;
    }

    public function getModesUsed(): array
    {
        return $this->modesUsed;
    }

    public function setModesUsed(array $modesUsed): self
    {
        $this->modesUsed = $modesUsed;
        return $this;
    }

    public function getTripsCount(): int
    {
        return $this->tripsCount;
    }

    public function setTripsCount(int $tripsCount): self
    {
        $this->tripsCount = $tripsCount;
        return $this;
    }

    public function getCo2Saved(): int
    {
        return $this->co2Saved;
    }

    public function setCo2Saved(int $co2Saved): self
    {
        $this->co2Saved = $co2Saved;
        return $this;
    }
    
public function getGarden(): array
{
    return $this->garden;
}

public function setGarden(array $garden): self
{
    $this->garden = $garden;
    return $this;
}

public function getId(): ?int
{
    return $this->id;
}

public function getUsername(): ?string
{
    return $this->username;
}

public function setUsername(string $username): static
{
    $this->username = $username;

    return $this;
}

public function getEmail(): ?string
{
    return $this->email;
}

public function setEmail(string $email): static
{
    $this->email = $email;

    return $this;
}

public function getPassword(): ?string
{
    return $this->password;
}

public function setPassword(string $password): static
{
    $this->password = $password;

    return $this;
}
}

