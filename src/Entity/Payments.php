<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'payments')]
#[ORM\Index(name: 'reservation_id', columns: ['reservation_id'])]
#[ORM\Index(name: 'payments_fk_users', columns: ['user_id'])]
#[ORM\Entity]
class Payments
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'payment_id', type: Types::INTEGER)]
    private ?int $paymentId = null;

    #[ORM\Column(name: 'amount', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(name: 'method', type: Types::STRING, length: 0)]
    private string $method;

    #[ORM\Column(
        name: 'payment_date', 
        type: Types::DATETIME_MUTABLE,
        options: ['default' => 'CURRENT_TIMESTAMP']
    )]
    private \DateTimeInterface $paymentDate;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Reservations::class)]
    #[ORM\JoinColumn(name: 'reservation_id', referencedColumnName: 'id')]
    private ?Reservations $reservation = null;

    public function getPaymentId(): ?int
    {
        return $this->paymentId;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    public function getPaymentDate(): \DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeInterface $paymentDate): static
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getReservation(): ?Reservations
    {
        return $this->reservation;
    }

    public function setReservation(?Reservations $reservation): static
    {
        $this->reservation = $reservation;
        return $this;
    }
}