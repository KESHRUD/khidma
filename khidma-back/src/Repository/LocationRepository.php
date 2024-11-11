<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function findNearby(float $latitude, float $longitude, float $radiusKm = 5.0): array
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l')
           ->addSelect(
               '(6371 * acos(cos(radians(:latitude)) * cos(radians(l.latitude)) * 
               cos(radians(l.longitude) - radians(:longitude)) + 
               sin(radians(:latitude)) * sin(radians(l.latitude)))) AS HIDDEN distance'
           )
           ->having('distance <= :radius')
           ->setParameter('latitude', $latitude)
           ->setParameter('longitude', $longitude)
           ->setParameter('radius', $radiusKm)
           ->orderBy('distance', 'ASC');

        return $qb->getQuery()->getResult();
    }
}