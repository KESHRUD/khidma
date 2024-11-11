<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function findNearby(float $latitude, float $longitude, float $radius = 5.0, ?int $category = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.id', 's.title', 's.price')
            ->addSelect('l.address', 'l.city', 'l.latitude', 'l.longitude')
            ->addSelect('c.name as category_name')
            ->addSelect(
                '(6371 * acos(cos(radians(:latitude)) * cos(radians(l.latitude)) * 
                cos(radians(l.longitude) - radians(:longitude)) + 
                sin(radians(:latitude)) * sin(radians(l.latitude)))) AS distance'
            )
            ->innerJoin('s.location', 'l')
            ->innerJoin('s.category', 'c')
            ->where('s.isActive = true')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->having('distance <= :radius')
            ->setParameter('radius', $radius)
            ->orderBy('distance', 'ASC');

        if ($category) {
            $qb->andWhere('s.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    public function search(?string $query = null, ?string $location = null, ?int $category = null, ?float $minPrice = null, ?float $maxPrice = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s', 'l', 'c')
            ->innerJoin('s.location', 'l')
            ->innerJoin('s.category', 'c')
            ->where('s.isActive = true');

        if ($query) {
            $qb->andWhere('s.title LIKE :query OR s.description LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($location) {
            $qb->andWhere('l.city LIKE :location OR l.address LIKE :location')
               ->setParameter('location', '%' . $location . '%');
        }

        if ($category) {
            $qb->andWhere('s.category = :category')
               ->setParameter('category', $category);
        }

        if ($minPrice) {
            $qb->andWhere('s.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $qb->andWhere('s.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Service $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Service $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}