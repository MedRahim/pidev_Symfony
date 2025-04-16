<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trips
 *
 * @ORM\Table(name="trips", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="trips_ibfk_1", columns={"transport_id"})})
 * @ORM\Entity
 */
class Trips
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
     * @var string
     *
     * @ORM\Column(name="departure", type="string", length=100, nullable=false)
     */
    private $departure;

    /**
     * @var string
     *
     * @ORM\Column(name="destination", type="string", length=100, nullable=false)
     */
    private $destination;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="departure_time", type="datetime", nullable=false)
     */
    private $departureTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="arrival_time", type="datetime", nullable=false)
     */
    private $arrivalTime;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $price;

    /**
     * @var string|null
     *
     * @ORM\Column(name="transport_name", type="string", length=255, nullable=true, options={"default"="NULL"})
     */
    private $transportName = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date", type="date", nullable=true, options={"default"="NULL"})
     */
    private $date = null;

    /**
     * @var float
     *
     * @ORM\Column(name="distance", type="float", precision=10, scale=0, nullable=false)
     */
    private float $distance = 0.0;
    /**
     * @var int
     *
     * @ORM\Column(name="capacity", type="integer", nullable=false, options={"default"="50"})
     */
    private $capacity = 50;

    /**
     * @var \TransportTypes
     *
     * @ORM\ManyToOne(targetEntity="TransportTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transport_id", referencedColumnName="transport_id")
     * })
     */
    private $transport;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeparture(): ?string
    {
        return $this->departure;
    }

    public function setDeparture(string $departure): static
    {
        $this->departure = $departure;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDepartureTime(): ?\DateTimeInterface
    {
        return $this->departureTime;
    }

    public function setDepartureTime(\DateTimeInterface $departureTime): static
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getArrivalTime(): ?\DateTimeInterface
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(\DateTimeInterface $arrivalTime): static
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getTransportName(): ?string
    {
        return $this->transportName;
    }

    public function setTransportName(?string $transportName): static
    {
        $this->transportName = $transportName;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): static
    {
        $this->distance = $distance;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getTransport(): ?TransportTypes
    {
        return $this->transport;
    }

    public function setTransport(?TransportTypes $transport): static
    {
        $this->transport = $transport;

        return $this;
    }

// Dans App\Entity\Trips.php
public function getTransportId(): ?int
{
    return $this->getTransport() ? $this->getTransport()->getTransportId() : null;
}
}
