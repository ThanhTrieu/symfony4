<?php

namespace App\Repository;

use App\Entity\Posts;
use App\Entity\PostDatas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Posts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Posts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Posts[]    findAll()
 * @method Posts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Posts::class);
    }

//    /**
//     * @return Posts[] Returns an array of Posts objects
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
    public function findOneBySomeField($value): ?Posts
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
     * Get Article by Id
     * author: TrieuNT
     * create date: 2018-10-18 10:33 AM
     * @param $postId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDetail($postId)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.authorId, a.title, a.slug, a.avatar, a.sapo, pd.content, pd.cates, pd.tags, a.publishedDate, a.reviewId, a.seoTitle, a.seoMetadesc')
            ->innerJoin(PostDatas::class, 'pd', 'WITH', 'a.postId = pd.postId')
            ->where('a.postId = :postId')
            ->setParameters([':postId' => $postId])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $data;
    }

    /**
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDetailPostBySlug($slug)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.authorId, a.title, a.slug, a.avatar, a.sapo, a.publishedDate, a.reviewId, a.seoTitle, a.seoMetadesc')
            ->where('a.slug = :slug')
            ->setParameters([':slug' => $slug])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $data;
    }
}
