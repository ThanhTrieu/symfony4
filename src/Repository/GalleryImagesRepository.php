<?php

namespace App\Repository;

use App\Entity\GalleryImages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GalleryImages|null find($id, $lockMode = null, $lockVersion = null)
 * @method GalleryImages|null findOneBy(array $criteria, array $orderBy = null)
 * @method GalleryImages[]    findAll()
 * @method GalleryImages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryImagesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GalleryImages::class);
    }

//    /**
//     * @return GalleryImages[] Returns an array of GalleryImages objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GalleryImages
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
