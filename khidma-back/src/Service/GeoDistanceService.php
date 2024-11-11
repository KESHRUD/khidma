<?php

namespace App\Service;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;

class GeoDistanceService
{
    private const EARTH_RADIUS = 6371; // Rayon de la Terre en kilomètres

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * self::EARTH_RADIUS, 2);
    }

    public function findLocationsInRadius(float $latitude, float $longitude, float $radius): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('l')
            ->addSelect(
                '(' . self::EARTH_RADIUS . ' * acos(
                    cos(radians(:latitude)) * cos(radians(l.latitude)) *
                    cos(radians(l.longitude) - radians(:longitude)) +
                    sin(radians(:latitude)) * sin(radians(l.latitude))
                )) AS HIDDEN distance'
            )
            ->from(Location::class, 'l')
            ->having('distance <= :radius')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('radius', $radius)
            ->orderBy('distance', 'ASC')
            ->getQuery()
            ->getResult();
    }
}