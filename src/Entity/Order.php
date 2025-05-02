<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\Type(type: 'integer', message: 'The ID must be an integer.')]
    #[Assert\Positive(message: 'The ID must be a positive number.')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'Order date cannot be null.')]
    #[Assert\Type(\DateTimeInterface::class, message: 'The date must be a valid DateTime object.')]
    #[Assert\LessThanOrEqual(
        value: 'now',
        message: 'Order date cannot be in the future.'
    )]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Status cannot be blank.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Status must be at least {{ limit }} characters long.',
        maxMessage: 'Status cannot be longer than {{ limit }} characters.'
    )]
    #[Assert\Choice(
        choices: ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
        message: 'Invalid status. Valid choices are: {{ choices }}.'
    )]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Assert\Count(
        min: 1,
        minMessage: 'An order must contain at least one item.'
    )]
    private Collection $orderItems;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Product')]
    #[ORM\JoinColumn(nullable: false)]
    private $product;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $confirmedAt = null;

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeInterface $confirmedAt): self
    {
        $this->confirmedAt = $confirmedAt;
        return $this;
    }

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->orderItems = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

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

    // Custom Validation
    #[Assert\Callback]
    public function validateCancellation(ExecutionContextInterface $context): void
    {
        if ($this->status === 'cancelled') {
            $now = new \DateTime();
            $interval = $now->diff($this->date);
           
            if ($interval->h < 24 && $interval->d === 0) {
                $context->buildViolation('Orders can only be cancelled within 24 hours of creation.')
                    ->atPath('status')
                    ->addViolation();
            }
        }
    }

    // Utility Methods
    public function getTotal(): float
    {
        return array_reduce(
            $this->orderItems->toArray(),
            fn(float $total, OrderItem $item) => $total + $item->getTotal(),
            0.0
        );
    }

    public function __toString(): string
    {
        return sprintf('Order #%d - %s', $this->id, $this->status);
    }
    
}

