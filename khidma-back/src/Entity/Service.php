<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use App\State\ServiceProcessor;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['service:read']],
    denormalizationContext: ['groups' => ['service:write']],
    operations: [
        new GetCollection(),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: ServiceProcessor::class
        ),
        new Get(),
        new Put(
            security: "is_granted('ROLE_USER') and object.getProvider() == user"
        )
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'partial',
    'description' => 'partial',
    'category.name' => 'exact',
    'provider.city' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['price', 'createdAt'])]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['service:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['service:read', 'service:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    #[Groups(['service:read', 'service:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['service:read', 'service:write'])]
    private ?string $price = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['service:read', 'service:write'])]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['service:read'])]
    private ?User $provider = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: Image::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[Groups(['service:read'])]
    private Collection $images;

    #[ORM\Column]
    #[Groups(['service:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['service:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['service:read', 'service:write'])]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['service:read', 'service:write'])]
    private ?Location $location = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: Review::class, orphanRemoval: true)]
    #[Groups(['service:read'])]
    private Collection $reviews;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true)]
    #[Groups(['service:read'])]
    private ?string $averageRating = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['service:read'])]
    private int $reviewsCount = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true)]
    #[Assert\Range(min: -90, max: 90)]
    #[Groups(['service:read', 'service:write'])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
    #[Assert\Range(min: -180, max: 180)]
    #[Groups(['service:read', 'service:write'])]
    private ?string $longitude = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->reviews = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getProvider(): ?User
    {
        return $this->provider;
    }

    public function setProvider(?User $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setService($this);
        }
        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getService() === $this) {
                $image->setService(null);
            }
        }
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
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

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setService($this);
        }
        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getService() === $this) {
                $review->setService(null);
            }
        }
        return $this;
    }

    public function getAverageRating(): ?string
    {
        return $this->averageRating;
    }

    public function setAverageRating(?string $averageRating): self
    {
        $this->averageRating = $averageRating;
        return $this;
    }

    public function getReviewsCount(): int
    {
        return $this->reviewsCount;
    }

    public function setReviewsCount(int $reviewsCount): self
    {
        $this->reviewsCount = $reviewsCount;
        return $this;
    }
}
