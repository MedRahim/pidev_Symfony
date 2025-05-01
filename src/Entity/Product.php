<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[Groups(['product:list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[Groups(['product:list'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Name cannot be null.")]
    #[Assert\Length(max: 255, maxMessage: "Name cannot exceed 255 characters.")]
    private ?string $name;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Reference cannot be null.")]
    #[Assert\Length(max: 255, maxMessage: "Reference cannot exceed 255 characters.")]
    private ?string $reference;

    #[Groups(['product:list'])]
    #[ORM\Column]
    #[Assert\NotBlank(message: "Price cannot be null.")]
    #[Assert\Type(type: 'float', message: "Price must be a valid number.")]
    #[Assert\PositiveOrZero(message: "Price must be zero or a positive value.")]
    private ?float $price;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Stock limit cannot be null.")]
    #[Assert\PositiveOrZero(message: "Stock limit must be zero or a positive value.")]
    private ?int $stockLimit;

    #[Groups(['product:list'])]
    #[ORM\Column]
    #[Assert\NotBlank(message: "Stock cannot be null.")]
    #[Assert\PositiveOrZero(message: "Stock must be zero or a positive value.")]
    private ?int $stock;

    #[Groups(['product:list'])]
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Image path cannot exceed 255 characters.")]
    private ?string $imagePath=null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Sold count cannot be null.")]
    #[Assert\PositiveOrZero(message: "Sold count must be zero or a positive value.")]
    private ?int $sold;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank(message: "Description cannot be null.")]
    #[Assert\Length(max: 500, maxMessage: "Description cannot exceed 500 characters.")]
    private ?string $description;

    #[Groups(['product:list'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Category cannot be null.")]
    #[Assert\Length(max: 100, maxMessage: "Category cannot exceed 100 characters.")]
    private ?string $category = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }
}
