<?php

namespace App\Repository;

use App\Entity\GalleryImages;
use App\Entity\PhotoGalleries;
use App\Entity\PhotoGalleriesImages;
use App\Entity\Posts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PhotoGalleries|null find($id, $lockMode = null, $lockVersion = null)
 * @method PhotoGalleries|null findOneBy(array $criteria, array $orderBy = null)
 * @method PhotoGalleries[]    findAll()
 * @method PhotoGalleries[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoGalleriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PhotoGalleries::class);
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-18 04:03 PM
     * get data gallery by postId
     * @param $postId
     * @return  array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function getDataGalleryPhotosByPostId($postId)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.galleryId, a.title, a.postId')
            ->where('a.postId = :postId')
            ->setParameters([':postId'=>$postId])
            ->getQuery()->getOneOrNullResult();
        return $data;
    }

//    /**
//     * @return PhotoGalleries[] Returns an array of PhotoGalleries objects
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
    public function findOneBySomeField($value): ?PhotoGalleries
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
     * get Galleries Photos Count
     * author: AnhPT4
     * date:   2018-10-30 10:21 AM
     * @param $langId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPhotoGalleriesCount()
    {
        $count = $this->createQueryBuilder('a')
            ->select('count(a.galleryId)')
            ->where('a.status = :status')
            ->setParameters([':status' => 1])
            ->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * get Galleries Photos Paging
     * author: AnhPT4
     * date:   2018-10-30 10:20 AM
     * @param $pageIndex
     * @param $pageSize
     * @param int $langId
     * @return array
     */
    public function getPhotoGalleriesPaging($pageIndex, $pageSize)
    {
        $startIndex = ($pageIndex - 1) * $pageSize;
        $data = $this->createQueryBuilder('a')
            ->select('a.galleryId,a.title,a.slug,a.createdDate,a.photoCount,a.avatar,a.postId,p.slug as post_slug')
            ->innerJoin(Posts::class, 'p', 'WITH', 'p.postId = a.postId')
            ->where('a.status = :status')
            ->setParameters([':status' => 1])
            ->orderBy('a.createdDate', 'DESC')
            ->setFirstResult($startIndex)
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();

        return $data;
    }

    /**
     * get Image Gallery By Id
     * author: AnhPT4
     * date:   2018-11-01 02:44 PM
     * @param $galleryId
     * @return array
     */
    public function getImageGalleryById($galleryId)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.galleryId,a.title,a.slug,a.postId,p.slug as post_slug,p.title as post_title')
            ->innerJoin(Posts::class, 'p', 'WITH', 'p.postId = a.postId')
            ->where('a.galleryId = :galleryId')
            ->setParameters([':galleryId'=>$galleryId])
            ->getQuery()->getOneOrNullResult();
        return $data;
    }
}
