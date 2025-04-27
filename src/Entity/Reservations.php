<?php
// src/Entity/Reservations.php

namespace App\Entity;

use App\Repository\ReservationsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=ReservationsRepository::class)
 * @ORM\Table(name="reservations")
 */
class Reservations
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CANCELED  = 'cancelled';

    public const PAYMENT_PAID     = 'paid';
    public const PAYMENT_PENDING  = 'pending';
    public const PAYMENT_FAILED   = 'failed';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $reservationTime = null;

    /**
     * @ORM\Column(type="string", length=20, options={"default":"pending"})
     */
    private ?string $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(type="integer")
     */
    private int $transportId;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(message="Le nombre de sièges est obligatoire.")
     * @Assert\Positive(message="Le nombre de sièges doit être un entier positif.")
     * @Assert\Range(
     *     min=1,
     *     notInRangeMessage="Vous devez réserver au moins {{ min }} siège."
     * )
     */
    private int $seatNumber;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @Assert\NotBlank(message="Le type de siège est obligatoire.")
     * @Assert\Choice(
     *     choices={"Standard", "Premium"},
     *     message="Le type de siège doit être Standard ou Premium."
     * )
     */
    private ?string $seatType = null;

    /**
     * @ORM\Column(type="string", length=20, options={"default":"pending"})
     */
    private ?string $paymentStatus = self::PAYMENT_PENDING;

    /**
     * @ORM\ManyToOne(targetEntity=Trips::class)
     * @ORM\JoinColumn(nullable=false, name="trip_id", referencedColumnName="id")
     */
    private ?Trips $trip = null;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class)
     * @ORM\JoinColumn(nullable=true, name="user_id", referencedColumnName="id")
     */
    private ?Users $user = null;

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

    public function getTransportId(): ?int
    {
        return $this->transportId;
    }

    public function setTransportId(int $transportId): static
    {
        $this->transportId = $transportId;
        return $this;
    }

    public function getSeatNumber(): ?int
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(int $seatNumber): static
    {
        $this->seatNumber = $seatNumber;
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

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;
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

    /**
     * @Assert\Callback
     */
    public function validateSeatNumber(ExecutionContextInterface $context): void
    {
        // Si aucun trip n’est lié, on skippe la vérif
        if (null === $this->trip) {
            return;
        }

        $capacity = $this->trip->getCapacity();
        if ($this->seatNumber > $capacity) {
            $context
                ->buildViolation('Vous ne pouvez pas réserver plus de {{ limit }} sièges.')
                ->atPath('seatNumber')
                ->setParameter('{{ limit }}', (string) $capacity)
                ->addViolation();
        }
    }
}
