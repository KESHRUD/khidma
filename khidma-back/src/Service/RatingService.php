<?php

namespace App\Service;

use App\Entity\Service;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class RatingService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {}

    public function addReview(Service $service, int $rating, string $comment): Review
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Vous devez être connecté pour laisser un avis.');
        }

        // Check if the user can add a review
        if (!$this->canUserReview($service)) {
            throw new \Exception('Vous avez déjà laissé un avis pour ce service.');
        }

        // Create and persist the review
        $review = new Review();
        $review->setService($service)
               ->setRating($rating)
               ->setComment($comment)
               ->setAuthor($user);

        $this->entityManager->persist($review);
        
        // Update service's rating and review count
        $this->updateServiceRating($service);

        // Flush all changes
        $this->entityManager->flush();

        return $review;
    }

    private function updateServiceRating(Service $service): void
    {
        $avgRating = $this->entityManager->createQueryBuilder()
            ->select('AVG(r.rating)')
            ->from(Review::class, 'r')
            ->where('r.service = :service')
            ->setParameter('service', $service)
            ->getQuery()
            ->getSingleScalarResult();

        $reviewsCount = $this->entityManager->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from(Review::class, 'r')
            ->where('r.service = :service')
            ->setParameter('service', $service)
            ->getQuery()
            ->getSingleScalarResult();

        // Set the new average rating and review count
        $service->setAverageRating($avgRating)
                ->setReviewsCount($reviewsCount);
    }

    public function canUserReview(Service $service): bool
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            return false;
        }

        // Check if the user has already reviewed this service
        $existingReview = $this->entityManager->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from(Review::class, 'r')
            ->where('r.service = :service')
            ->andWhere('r.author = :user')
            ->setParameters([
                'service' => $service,
                'user' => $user
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $existingReview == 0;
    }
}
