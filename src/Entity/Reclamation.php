<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Client ID cannot be null.')]
    #[Assert\Type(type: 'integer', message: 'Client ID must be a valid number.')]
    private ?int $clientId = null;

    #[ORM\Column(length: 255)]
    private ?string $datee = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Description cannot be blank.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Description must be at least {{ limit }} characters long.'
    )]
    private ?string $description = null;

    #[ORM\Column(name: 'state')]  // Maps to `state` in DB
    private ?bool $state = false;  // Default: false (unresolved)

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Complaint type cannot be blank.')]
    #[Assert\Choice([
        'Problème d\'application',
        'Réclamation Service administratif',
        'Réclamation service de transport',
        'Réclamation service hospitalier',
        'Réclamation service supermarché en ligne',
        'Autre problème'
    ], message: 'Invalid complaint type.')]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]  // Made nullable (optional)
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]  // Will be set by API later
    private ?string $priorite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Email cannot be blank.')]
    #[Assert\Email(message: 'Invalid email format.')]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'reclamation', targetEntity: Reponse::class, cascade: ['persist', 'remove'])]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getDatee(): ?string
    {
        return $this->datee;
    }

    public function setDatee(string $datee): static
    {
        $this->datee = $datee;

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

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setReclamation($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getReclamation() === $this) {
                $reponse->setReclamation(null);
            }
        }

        return $this;
    }
}
