<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'badges')]
#[ORM\Entity]
class Badges
{
    #[ORM\Column(name: 'id', type: 'string', length: 50, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private string $id;

    #[ORM\Column(
        name: 'description', 
        type: 'string', 
        length: 255, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $description = null;

    #[ORM\Column(
        name: 'image_path', 
        type: 'string', 
        length: 255, 
        nullable: true, 
        options: ['default' => null]
    )]
    private ?string $imagePath = null;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }
}