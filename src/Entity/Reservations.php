<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Reservations
 *
 * @ORM\Table(name="reservations", indexes={@ORM\Index(name="fk_reservation_trip", columns={"trip_id"}), @ORM\Index(name="transport_id", columns={"transport_id"}), @ORM\Index(name="reservations_fk_users", columns={"user_id"})})
 * @ORM\Entity
 */
class Reservations
{
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
    private $reservationTime = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=true, options={"default"="'Pending'"})
     */
    private $status = '\'Pending\'';

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
     */
    private $seatNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="payment_status", type="string", length=20, nullable=true, options={"default"="'Pending'"})
     */
    private $paymentStatus = '\'Pending\'';

    /**
     * @var string|null
     *
     * @ORM\Column(name="seat_type", type="string", length=20, nullable=true, options={"default"="'Standard'"})
     */
    private $seatType = '\'Standard\'';

    /**
     * @var \Trips
     *
     * @ORM\ManyToOne(targetEntity="Trips")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     * })
     */
    private $trip;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
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
