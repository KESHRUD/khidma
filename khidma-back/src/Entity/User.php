<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use App\State\UserPasswordHasher;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            uriTemplate: '/users',
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:create']],
            validationContext: ['groups' => ['Default', 'user:create']],
            security: "is_granted('PUBLIC_ACCESS')",
            securityMessage: 'Only public access allowed.',
            processor: UserPasswordHasher::class
        ),
        new Post(
            uriTemplate: '/login_check',
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:login']],
            validationContext: ['groups' => ['Default', 'user:login']],
            security: "is_granted('PUBLIC_ACCESS')",
            name: 'login'
        ),
        new Get(
            normalizationContext: ['groups' => ['user:read', 'user:read:full']],
            security: "is_granted('ROLE_USER') and object == user"
        ),
        new Put(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:update']],
            security: "is_granted('ROLE_USER') and object == user",
            validationContext: ['groups' => ['Default', 'user:update']]
        ),
        new Patch(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:update']],
            security: "is_granted('ROLE_USER') and object == user",
            validationContext: ['groups' => ['Default', 'user:update']]
        )
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'user_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:create', 'user:update', 'user:login'])]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['user:create', 'user:login'])]
    #[Assert\Length(
        min: 8,
        max: 50,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères',
        groups: ['user:create', 'user:login']
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/',
        message: 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial',
        groups: ['user:create', 'user:login']
    )]
    #[Groups(['user:create', 'user:update', 'user:login'])]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(min: 2, max: 50)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(min: 2, max: 50)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(min: 10, max: 20)]
    #[Assert\Regex(
        pattern: '/^[0-9\+]+$/',
        message: 'Le numéro de téléphone ne doit contenir que des chiffres et le symbole +'
    )]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $phoneNumber = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    #[Assert\Length(max: 100)]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    #[Assert\Length(max: 100)]
    private ?string $state = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    #[Assert\Length(max: 10)]
    private ?string $zipCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    #[Assert\Length(max: 100)]
    private ?string $country = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true)]
    #[Assert\Range(min: -90, max: 90)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
    #[Assert\Range(min: -180, max: 180)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $longitude = null;

    #[Vich\UploadableField(mapping: 'user_avatar', fileNameProperty: 'avatarName')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png'],
        mimeTypesMessage: 'Veuillez uploader une image valide (JPEG ou PNG)'
    )]
    #[Groups(['user:create', 'user:update'])]
    private ?File $avatarFile = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read'])]
    private ?string $avatarName = null;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: Service::class, orphanRemoval: true)]
    #[Groups(['user:read:full'])]
    private Collection $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
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

    public function getEmail(): ?string
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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setProvider($this);
        }
        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            if ($service->getProvider() === $this) {
                $service->setProvider(null);
            }
        }
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
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

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarFile(?File $avatarFile = null): void
    {
        $this->avatarFile = $avatarFile;
        if ($avatarFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getAvatarName(): ?string
    {
        return $this->avatarName;
    }

    public function setAvatarName(?string $avatarName): void
    {
        $this->avatarName = $avatarName;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getFullName(): ?string
    {
        if ($this->firstName && $this->lastName) {
            return $this->firstName . ' ' . $this->lastName;
        }
        return null;
    }

    public function getFullAddress(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zipCode,
            $this->country
        ]);
        
        if (empty($parts)) {
            return null;
        }
        
        return implode(', ', $parts);
    }
}