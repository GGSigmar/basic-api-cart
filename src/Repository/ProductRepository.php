<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return QueryBuilder
     */
    public function getAllActiveProductsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isDeleted = :false')
            ->setParameter('false', false)
            ->orderBy('p.id', 'ASC')
        ;
    }

    /**
     * @param int $productId
     *
     * @return Product|null
     */
    public function getActiveProductById(int $productId): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->andWhere('p.isDeleted = :false')
            ->setParameter('id', $productId)
            ->setParameter('false', false)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
