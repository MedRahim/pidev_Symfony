<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type("integer")]
    #[Assert\Positive]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type("integer")]
    #[Assert\Positive]
    private ?int $productId = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type("integer")]
    #[Assert\Positive]
    private ?int $quantity = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type("float")]
    #[Assert\PositiveOrZero]
    private ?float $priceTotal = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPriceTotal(): ?float
    {
        return $this->priceTotal;
    }

    public function setPriceTotal(float $priceTotal): static
    {
        $this->priceTotal = $priceTotal;

        return $this;
    }
}
