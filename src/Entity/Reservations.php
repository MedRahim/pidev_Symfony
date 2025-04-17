<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(
    name: 'reservations',
    indexes: [
        new ORM\Index(name: 'fk_reservation_trip', columns: ['trip_id']),
        new ORM\Index(name: 'transport_id', columns: ['transport_id']),
        new ORM\Index(name: 'reservations_fk_users', columns: ['user_id'])
    ]
)]
#[ORM\Entity(repositoryClass: "App\Repository\ReservationsRepository")]
class Reservations
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELED = 'canceled';

    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(
        name: 'reservation_time',
        type: Types::DATETIME_MUTABLE,
        nullable: true,
        options: ['default' => null]
    )]
    private ?\DateTimeInterface $reservationTime = null;

    #[ORM\Column(
        name: 'status',
        type: Types::STRING,
        length: 20,
        nullable: true,
        options: ['default' => self::STATUS_PENDING]
    )]
    #[Assert\Choice(
        choices: [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELED],
        message: 'Statut invalide'
    )]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'transport_id', type: Types::INTEGER)]
    private int $transportId;

    #[ORM\Column(name: 'seat_number', type: Types::INTEGER)]
    #[Assert\NotBlank(message: 'Le nombre de sièges est obligatoire')]
    #[Assert\Positive(message: 'Le nombre de sièges doit être positif')]
    #[Assert\LessThanOrEqual(
        value: 10,
        message: 'Vous ne pouvez pas réserver plus de 10 sièges'
    )]
    private int $seatNumber;

    #[ORM\Column(
        name: 'payment_status',
        type: Types::STRING,
        length: 20,
        nullable: true,
        options: ['default' => self::PAYMENT_PENDING]
    )]
    #[Assert\Choice(
        choices: [self::PAYMENT_PENDING, self::PAYMENT_PAID, self::PAYMENT_FAILED],
        message: 'Statut de paiement invalide'
    )]
    private ?string $paymentStatus = self::PAYMENT_PENDING;

    #[ORM\Column(
        name: 'seat_type',
        type: Types::STRING,
        length: 20,
        nullable: true,
        options: ['default' => 'Standard']
    )]
    #[Assert\NotBlank(message: 'Le type de siège est obligatoire')]
    #[Assert\Choice(
        choices: ['Standard', 'Premium'],
        message: 'Type de siège invalide'
    )]
    private ?string $seatType = 'Standard';

    #[ORM\ManyToOne(targetEntity: Trips::class)]
    #[ORM\JoinColumn(name: 'trip_id', referencedColumnName: 'id', nullable: true)]
    private ?Trips $trip = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?Users $user = null;

    // Getters and setters remain the same as before
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservationTime(): ?\DateTimeInterface
    {
        return $this->reservationTime;
    }

    public function setReservationTime(?\DateTimeInterface $reservationTime): static
    {
        $this->reservationTime = $reservationTime;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getTransportId(): int
    {
        return $this->transportId;
    }

    public function setTransportId(int $transportId): static
    {
        $this->transportId = $transportId;
        return $this;
    }

    public function getSeatNumber(): int
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(int $seatNumber): static
    {
        $this->seatNumber = $seatNumber;
        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getSeatType(): ?string
    {
        return $this->seatType;
    }

    public function setSeatType(?string $seatType): static
    {
        $this->seatType = $seatType;
        return $this;
    }

    public function getTrip(): ?Trips
    {
        return $this->trip;
    }

    public function setTrip(?Trips $trip): static
    {
        $this->trip = $trip;
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
}