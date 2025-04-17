<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(
    name: 'users',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'email', columns: ['email'])
    ]
)]
#[ORM\Entity]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'username', type: 'string', length: 255, nullable: false)]
    private string $username = '';

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: false)]
    private string $email = '';

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
    private string $password = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
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
}