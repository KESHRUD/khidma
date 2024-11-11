<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    operations: [
        new Get(
            normalizationContext: ['groups' => ['image:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['image:read']]
        ),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            normalizationContext: ['groups' => ['image:read']],
            denormalizationContext: ['groups' => ['image:write']],
            validationContext: ['groups' => ['Default', 'image:write']],
            security: "is_granted('ROLE_USER')"
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object.getOwner() == user"
        )
    ]
)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['image:read', 'service:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['image:read', 'service:read'])]
    private ?string $filePath = null;

    #[Vich\UploadableField(mapping: 'service_images', fileNameProperty: 'filePath')]
    #[Assert\NotNull(groups: ['image:write'])]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        groups: ['image:write']
    )]
    #[Groups(['image:write'])]
    private ?File $file = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['image:read'])]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'images')]
    #[Groups(['image:read', 'image:write'])]
    private ?Service $service = null;

    #[ORM\Column]
    #[Groups(['image:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;
        if ($file) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;
        return $this;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}