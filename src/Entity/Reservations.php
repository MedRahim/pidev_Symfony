<?php

// src/Entity/Reservations.php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reservations
 *
 * @ORM\Table(name="reservations", indexes={@ORM\Index(name="fk_reservation_trip", columns={"trip_id"}), @ORM\Index(name="transport_id", columns={"transport_id"}), @ORM\Index(name="reservations_fk_users", columns={"user_id"})})
 * @ORM\Entity
 *@ORM\Entity(repositoryClass="App\Repository\ReservationsRepository")
 */
class Reservations
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CANCELED  = 'canceled';

    public const PAYMENT_PAID     = 'paid';
    public const PAYMENT_PENDING  = 'pending';
    public const PAYMENT_FAILED   = 'failed';
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="reservation_time", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $reservationTime = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=true, options={"default"="'Pending'"})
     * @Assert\Choice(
     *     choices={"Pending", "Confirmed", "Cancelled"},
     *     message="Statut invalide"
     * )
     */
    private $status = 'Pending';

    /**
     * @var int
     *
     * @ORM\Column(name="transport_id", type="integer", nullable=false)
     */
    private $transportId;

    /**
     * @var int
     *
     * @ORM\Column(name="seat_number", type="integer", nullable=false)
     * @Assert\NotBlank(message="Le nombre de sièges est obligatoire")
     * @Assert\Positive(message="Le nombre de sièges doit être positif")
     * @Assert\LessThanOrEqual(
     *     value=10,
     *     message="Vous ne pouvez pas réserver plus de 10 sièges"
     * )
     */
    private $seatNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="payment_status", type="string", length=20, nullable=true, options={"default"="'Pending'"})
     * @Assert\Choice(
     *     choices={"Pending", "Paid", "Cancelled", "Refunded"},
     *     message="Statut de paiement invalide"
     * )
     */
    private $paymentStatus = 'Pending';

    /**
     * @var string|null
     *
     * @ORM\Column(name="seat_type", type="string", length=20, nullable=true, options={"default"="'Standard'"})
     * @Assert\NotBlank(message="Le type de siège est obligatoire")
     * @Assert\Choice(
     *     choices={"Standard", "Premium"},
     *     message="Type de siège invalide"
     * )
     */
    private $seatType = 'Standard';

    /**
     * @var \Trips
     *
     * @ORM\ManyToOne(targetEntity="Trips")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="trip_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $trip;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;


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
