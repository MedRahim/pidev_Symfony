<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Comment;
use App\Entity\PostLike;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "The title cannot be blank.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "The title must exceed {{ limit }} characters.",
        maxMessage: "The title cannot exceed {{ limit }} characters."
    )]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "The content cannot be blank.")]
    #[Assert\Length(
        min: 10,
        minMessage: "The title must exceed {{ limit }} characters."
    )]
    private string $content;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $postDate;

    #[ORM\Column(type: 'boolean')]
    private bool $approved;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "The image URL cannot exceed {{ limit }} characters."
    )]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "The category cannot exceed {{ limit }} characters."
    )]
    private ?string $category = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'blogPost', targetEntity: Comment::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'blogPost', targetEntity: PostLike::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $likes;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->postDate  = new \DateTime();
        $this->approved  = false;
        $this->updatedAt = new \DateTime();
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // Getter/Setter methods follow:

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getPostDate(): \DateTime
    {
        return $this->postDate;
    }
    public function setPostDate(\DateTime $postDate): self
    {
        $this->postDate = $postDate;
        return $this;
    }
    public function isApproved(): bool
    {
        return $this->approved;
    }
    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;
        return $this;
    }
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }
    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }
    public function getCategory(): ?string
    {
        return $this->category;
    }
    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setBlogPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getBlogPost() === $this) {
                $comment->setBlogPost(null);
            }
        }

        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PostLike $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setBlogPost($this);
        }

        return $this;
    }

    public function removeLike(PostLike $like): self
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getBlogPost() === $this) {
                $like->setBlogPost(null);
            }
        }

        return $this;
    }
}
