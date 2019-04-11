<?php

namespace App\Repository;

use App\Entity\Pages;
use App\Entity\AdminUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Pages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pages[]    findAll()
 * @method Pages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Pages::class);
    }

//    /**
//     * @return Pages[] Returns an array of Pages objects
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
    public function findOneBySomeField($value): ?Pages
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    /**
     * Get Detail page
     * author: TrieuNT
     * create date: 2018-11-29 11:49 AM
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDetailPage($slug)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.pageId, a.authorId, a.title, a.slug, a.avatar, a.sapo, a.content, a.publishedDate, a.reviewId, p.title parentTitle, p.slug parentSlug, c.fullname, c.slug userSlug')
            ->leftJoin(Pages::class, 'p', 'WITH', 'a.parentId = p.pageId')
            ->leftJoin(AdminUsers::class, 'c', 'WITH', 'c.userId = a.creatorId')
            ->where('a.slug = :slug')
            ->setParameters([':slug' => $slug])
            ->getQuery()->getOneOrNullResult();
        return $data;
    }
}
