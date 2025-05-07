<?php

namespace App\Entity\Ines;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;



#[ORM\Entity]
#[Vich\Uploadable]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idMedecin", type: "integer")]
    private int $idMedecin;

    #[ORM\Column(name: "nomM", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s'-]+$/u",
        message: "Le nom ne doit contenir que des lettres."
    )]
    private string $nomM;

    #[ORM\Column(name: "prenomM", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s'-]+$/u",
        message: "Le prénom ne doit contenir que des lettres."
    )]
    private string $prenomM;

    #[ORM\Column(name: "specialite", type: "string", length: 255)]
    #[Assert\NotBlank(message: "La spécialité est obligatoire.")]
    private string $specialite;

    #[ORM\Column(name: "contact", type: "integer")]
    #[Assert\NotBlank(message: "Le contact est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[0-9]{8,}$/",
        message: "Le contact doit contenir au moins 8 chiffres."
    )]
    private int $contact;

    #[ORM\ManyToOne(targetEntity: ServiceHospitalier::class, inversedBy: "medecins")]
    #[ORM\JoinColumn(name: "idService", referencedColumnName: "idService", nullable: false)]
    #[Assert\NotNull(message: "Le service hospitalier est obligatoire.")]
    private ?ServiceHospitalier $service = null;
    // --- Getters et Setters ---

    #[ORM\OneToMany(mappedBy: "medecin", targetEntity: Rendezvous::class)]
private Collection $rendezvous;



#[Vich\UploadableField(mapping: 'medecin', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    
    public function getIdMedecin(): int
    {
        return $this->idMedecin;
    }

    public function getNomM(): string
    {
        return $this->nomM;
    }

    public function setNomM(string $nomM): void
    {
        $this->nomM = $nomM;
    }

    public function getPrenomM(): string
    {
        return $this->prenomM;
    }

    public function setPrenomM(string $prenomM): void
    {
        $this->prenomM = $prenomM;
    }

    public function getSpecialite(): string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): void
    {
        $this->specialite = $specialite;
    }

    public function getContact(): int
    {
        return $this->contact;
    }

    public function setContact(int $contact): void
    {
        $this->contact = $contact;
    }

    public function getService(): ?ServiceHospitalier
    {
        return $this->service;
    }

    public function setService(?ServiceHospitalier $service): void
    {
        $this->service = $service;
    }

    public function getRendezvous(): Collection
{
    return $this->rendezvous;
}

public function __construct()
{
    $this->rendezvous = new ArrayCollection();
}




public function setImageFile(?File $imageFile = null): void
{
    $this->imageFile = $imageFile;

    if (null !== $imageFile) {
        // Vous n'avez plus besoin de mettre à jour une date si vous n'utilisez pas l'attribut `updatedAt`
    }
}


    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getImageOrDefault(): string
{
    return $this->getImageName() ?: 'default-user.png';
}





}