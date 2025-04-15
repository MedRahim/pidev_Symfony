<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Name cannot be null.")]
    #[Assert\Length(max: 255, maxMessage: "Name cannot exceed 255 characters.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Reference cannot be null.")]
    private ?string $reference = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Price cannot be null.")]
    #[Assert\Type(type: 'float', message: "Price must be a valid number.")]
    #[Assert\PositiveOrZero(message: "Price must be zero or a positive value.")]
    private ?float $price = 0.0;

    #[ORM\Column]
    #[Assert\NotNull(message: "Stock limit cannot be null.")]
    #[Assert\PositiveOrZero(message: "Stock limit must be zero or a positive value.")]
    private ?int $stockLimit = 0;

    #[ORM\Column]
    #[Assert\NotNull(message: "Stock cannot be null.")]
    #[Assert\PositiveOrZero(message: "Stock must be zero or a positive value.")]
    private ?int $stock = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Sold count cannot be null.")]
    #[Assert\PositiveOrZero(message: "Sold count must be zero or a positive value.")]
    private ?int $sold = 0;

    #[ORM\Column(length: 500)]
    #[Assert\NotNull(message: "Description cannot be null.")]
    #[Assert\Length(
        max: 500,
        maxMessage: "Description cannot exceed 500 characters."
    )]
    private ?string $description = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStockLimit(): ?int
    {
        return $this->stockLimit;
    }

    public function setStockLimit(int $stockLimit): static
    {
        $this->stockLimit = $stockLimit;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

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

    public function getSold(): ?int
    {
        return $this->sold;
    }

    public function setSold(int $sold): static
    {
        $this->sold = $sold;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
