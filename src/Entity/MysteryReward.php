<?php
// src/Entity/MysteryReward.php
namespace App\Entity;

use App\Repository\MysteryRewardRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: MysteryRewardRepository::class)]
class MysteryReward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $grantedAt = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;
    public function getReadableType(): string
    {
        return match ($this->type) {
            'small_discount'  => 'Petit bonus (5% de réduction)',
            'medium_discount' => 'Bonus moyen (10% de réduction)',
            'big_discount'    => 'Gros bonus (20% de réduction)',
            default           => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
    // Getters et setters...
    public function getId(): ?int { return $this->id; }
    public function getUser(): ?Users { return $this->user; }
    public function setUser(?Users $user): self { $this->user = $user; return $this; }
    public function getGrantedAt(): ?\DateTimeInterface { return $this->grantedAt; }
    public function setGrantedAt(\DateTimeInterface $grantedAt): self { $this->grantedAt = $grantedAt; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getTier(): ?string { return $this->tier; }
    public function setTier(string $tier): self { $this->tier = $tier; return $this; }

}