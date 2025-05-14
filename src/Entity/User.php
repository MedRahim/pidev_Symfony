<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['CIN'], message: 'There is already an account with this CIN')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 8, unique: true)]
    #[Assert\NotBlank(message: 'CIN cannot be blank.')]
    #[Assert\Length(
        min: 8,
        max: 8,
        exactMessage: 'CIN must be exactly {{ limit }} characters long.'
    )]
    #[Assert\Regex(
        pattern: '/^[0-9]*$/',
        message: 'CIN must contain only numbers.'
    )]
    private ?string $CIN = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Name cannot be blank.')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Name must be at least {{ limit }} characters long.',
        maxMessage: 'Name cannot be longer than {{ limit }} characters.'
    )]
    private ?string $name = null;

    #[ORM\Column(name: 'Email',length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Email cannot be blank.')]
    #[Assert\Email(message: 'The email "{{ value }}" is not a valid email.')]
    private ?string $Email = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Password cannot be blank.')]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 15)]
    #[Assert\NotBlank(message: 'Phone number cannot be blank.')]
    #[Assert\Length(
        min: 8,
        max: 15,
        minMessage: 'Phone number must be at least {{ limit }} characters long.',
        maxMessage: 'Phone number cannot be longer than {{ limit }} characters.'
    )]
    #[Assert\Regex(
        pattern: '/^[0-9+]*$/',
        message: 'Phone number must contain only numbers and +.'
    )]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Address cannot be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Address cannot be longer than {{ limit }} characters.'
    )]
    private ?string $address = null;

    #[ORM\Column]
    private bool $isActive = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'Birthday cannot be blank.')]
    #[Assert\LessThan(
        value: '-18 years',
        message: 'You must be at least 18 years old.'
    )]
    private ?\DateTimeInterface $birthday = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $accountCreationDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $lastLoginDate = null;

    #[ORM\Column]
    private int $failedLoginAttempts = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $bio = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleAuthenticatorSecret = null;

    #[ORM\Column]
    private bool $isGoogleAuthenticatorEnabled = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Trips::class, orphanRemoval: true)]
    private Collection $trips;

    public function __construct()
    {
        $this->trips = new ArrayCollection();
        $this->accountCreationDate = new \DateTimeImmutable();
        $this->lastLoginDate = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCIN(): ?string
    {
        return $this->CIN;
    }

    public function setCIN(string $CIN): static
    {
        $this->CIN = $CIN;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->Email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): static
    {
        $this->birthday = $birthday;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getAccountCreationDate(): ?\DateTimeInterface
    {
        return $this->accountCreationDate;
    }

    public function setAccountCreationDate(\DateTimeInterface $accountCreationDate): static
    {
        $this->accountCreationDate = $accountCreationDate;
        return $this;
    }

    public function getLastLoginDate(): ?\DateTimeInterface
    {
        return $this->lastLoginDate;
    }

    public function setLastLoginDate(\DateTimeInterface $lastLoginDate): static
    {
        $this->lastLoginDate = $lastLoginDate;
        return $this;
    }

    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function setFailedLoginAttempts(int $failedLoginAttempts): static
    {
        $this->failedLoginAttempts = $failedLoginAttempts;
        return $this;
    }

    public function incrementFailedLoginAttempts(): static
    {
        $this->failedLoginAttempts++;
        return $this;
    }

    public function resetFailedLoginAttempts(): static
    {
        $this->failedLoginAttempts = 0;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->isGoogleAuthenticatorEnabled;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    public function setIsGoogleAuthenticatorEnabled(bool $isGoogleAuthenticatorEnabled): void
    {
        $this->isGoogleAuthenticatorEnabled = $isGoogleAuthenticatorEnabled;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @return Collection<int, Trips>
     */
    public function getTrips(): Collection
    {
        return $this->trips;
    }

    public function addTrip(Trips $trip): static
    {
        if (!$this->trips->contains($trip)) {
            $this->trips->add($trip);
            $trip->setUser($this);
        }

        return $this;
    }

    public function removeTrip(Trips $trip): static
    {
        if ($this->trips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getUser() === $this) {
                $trip->setUser(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName() ?? 'User #'.$this->getId();
    }
}