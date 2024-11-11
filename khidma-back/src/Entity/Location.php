<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['location:read']],
    denormalizationContext: ['groups' => ['location:write']],
    operations: [
        new GetCollection(
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            validationContext: ['groups' => ['Default', 'location:create']]
        ),
        new Get(
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Put(
            security: "is_granted('ROLE_USER')",
            validationContext: ['groups' => ['Default', 'location:update']]
        )
    ]
)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['location:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['location:read', 'location:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['location:read', 'location:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['location:read', 'location:write'])]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?string $state = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Groups(['location:read', 'location:write'])]
    private ?string $zipCode = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['location:read', 'location:write'])]
    private ?string $country = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8)]
    #[Assert\NotBlank]
    #[Assert\Range(min: -90, max: 90)]
    #[Groups(['location:read', 'location:write'])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8)]
    #[Assert\NotBlank]
    #[Assert\Range(min: -180, max: 180)]
    #[Groups(['location:read', 'location:write'])]
    private ?string $longitude = null;

    #[ORM\Column]
    #[Groups(['location:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['location:read'])]
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

    // Getters and setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;
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

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zipCode,
            $this->country
        ]);
        return implode(', ', $parts);
    }
}