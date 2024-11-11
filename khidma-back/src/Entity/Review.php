<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\State\ReviewProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['review:read']],
    denormalizationContext: ['groups' => ['review:write']],
    operations: [
        new GetCollection(),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: ReviewProcessor::class
        ),
        new Get(
            security: "is_granted('ROLE_USER')"
        ),
        new Put(
            security: "is_granted('ROLE_USER') and object.getAuthor() == user"
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.getAuthor() == user"
        )
    ]
)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review:read', 'service:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['review:read', 'review:write'])]
    private ?Service $service = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['review:read'])]
    private ?User $author = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 5)]
    #[Groups(['review:read', 'review:write', 'service:read'])]
    private ?int $rating = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 1000)]
    #[Groups(['review:read', 'review:write', 'service:read'])]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups(['review:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['review:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['review:read'])]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }
}