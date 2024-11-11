<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['message:read']],
    denormalizationContext: ['groups' => ['message:write']],
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            paginationEnabled: true,
            paginationItemsPerPage: 20
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            validationContext: ['groups' => ['Default', 'message:create']]
        ),
        new Get(
            security: "is_granted('VIEW_MESSAGE', object)"
        ),
        new Delete(
            security: "is_granted('DELETE_MESSAGE', object)"
        )
    ]
)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['message:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 1000)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:read'])]
    private ?User $sender = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['message:read', 'message:write'])]
    private ?User $recipient = null;

    #[ORM\Column]
    #[Groups(['message:read'])]
    private ?bool $isRead = false;

    #[ORM\Column]
    #[Groups(['message:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['message:read'])]
    private ?\DateTimeImmutable $readAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getter and Setter for id
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter and Setter for content
    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    // Getter and Setter for sender
    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    // Getter and Setter for recipient
    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    // Getter and Setter for isRead
    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    // Getter and Setter for createdAt
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Getter and Setter for readAt
    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): self
    {
        $this->readAt = $readAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
