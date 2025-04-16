<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TripsRepository")
 * @ORM\Table(name="trips")
 * @ORM\HasLifecycleCallbacks()
 */
class Trips
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Le lieu de départ est obligatoire")
     * @Assert\Length(
     *      min=2,
     *      max=100,
     *      minMessage="Le lieu de départ doit faire au moins {{ limit }} caractères",
     *      maxMessage="Le lieu de départ ne peut pas dépasser {{ limit }} caractères"
     * )
     * @Assert\Regex(
     *     pattern="/^[a-zA-ZÀ-ÿ\s\-\']+$/u",
     *     message="Le lieu ne doit contenir que des lettres, espaces et tirets"
     * )
     */
    private $departure;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="La destination est obligatoire")
     * @Assert\Length(
     *      min=2,
     *      max=100,
     *      minMessage="La destination doit faire au moins {{ limit }} caractères",
     *      maxMessage="La destination ne peut pas dépasser {{ limit }} caractères"
     * )
     * @Assert\Regex(
     *     pattern="/^[a-zA-ZÀ-ÿ\s\-\']+$/u",
     *     message="La destination ne doit contenir que des lettres, espaces et tirets"
     * )
     */
    private $destination;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="L'heure de départ est obligatoire")
     * @Assert\GreaterThan(
     *     value="now",
     *     message="La date de départ doit être dans le futur"
     * )
     */
    private $departureTime;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="L'heure d'arrivée est obligatoire")
     * @Assert\GreaterThan(
     *     propertyPath="departureTime",
     *     message="L'heure d'arrivée doit être après l'heure de départ"
     * )
     */
    private $arrivalTime;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotBlank(message="Le prix est obligatoire")
     * @Assert\Positive(message="Le prix doit être positif")
     * @Assert\LessThanOrEqual(
     *     value=10000,
     *     message="Le prix ne peut pas dépasser {{ compared_value }}€"
     * )
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $transportName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero(message="La distance doit être positive")
     */
    private $distance = 0.0;

    /**
     * @ORM\Column(type="integer", options={"default": 50})
     * @Assert\NotBlank(message="La capacité est obligatoire")
     * @Assert\Positive(message="La capacité doit être positive")
     * @Assert\LessThanOrEqual(
     *     value=200,
     *     message="La capacité ne peut pas dépasser {{ compared_value }} places"
     * )
     */
    private $capacity = 50;

    /**
     * @ORM\ManyToOne(targetEntity="TransportTypes")
     * @ORM\JoinColumn(name="transport_id", referencedColumnName="transport_id")
     */
    private $transport;

    /**
     * @ORM\PrePersist
     */
    public function setTimestamps(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // Getters et Setters...

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
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

    public function getTransportId(): ?int
    {
        return $this->getTransport() ? $this->getTransport()->getTransportId() : null;
    }
}