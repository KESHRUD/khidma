<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findActiveWithServices()
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->leftJoin('c.services', 's')
            ->addSelect('s')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySearchTerm(string $term)
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.name) LIKE :term')
            ->orWhere('LOWER(c.description) LIKE :term')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getCategoryStatistics()
    {
        return $this->createQueryBuilder('c')
            ->select('c.name')
            ->addSelect('COUNT(s.id) as serviceCount')
            ->leftJoin('c.services', 's')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}