<?php

namespace App\Repository;

use App\Entity\PostReviews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PostReviews|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostReviews|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostReviews[]    findAll()
 * @method PostReviews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostReviewsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PostReviews::class);
    }

//    /**
//     * @return PostReviews[] Returns an array of PostReviews objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PostReviews
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
