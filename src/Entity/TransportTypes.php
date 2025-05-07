<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'transport_types')]
#[ORM\Index(name: 'idx_transport_id', columns: ['transport_id'])]
#[ORM\Entity]
class TransportTypes
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'transport_id', type: Types::INTEGER)]
    private ?int $transportId = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 50, nullable: false)]
    private string $name;

    #[ORM\Column(
        name: 'description', 
        type: Types::TEXT, 
        length: 65535, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $description = null;

    #[ORM\Column(name: 'capacity', type: Types::INTEGER, nullable: false)]
    private int $capacity;

    public function __toString(): string
    {
        return $this->getName(); // Éviter les relations dans __toString()
    }
    public function getTransportId(): ?int
    {
        return $this->transportId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;
        return $this;
    }
}