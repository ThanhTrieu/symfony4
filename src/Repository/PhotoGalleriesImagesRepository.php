<?php

namespace App\Repository;

use App\Entity\PhotoGalleriesImages;
use App\Entity\GalleryImages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PhotoGalleriesImages|null find($id, $lockMode = null, $lockVersion = null)
 * @method PhotoGalleriesImages|null findOneBy(array $criteria, array $orderBy = null)
 * @method PhotoGalleriesImages[]    findAll()
 * @method PhotoGalleriesImages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoGalleriesImagesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PhotoGalleriesImages::class);
    }
    /**
     * author: TrieuNT
     * date:   2018-10-12 08:53 AM
     * @param $galleryId
     * @return array
     */
    public function getListAllImageByGalleryById($galleryId)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.id, a.galleryId, a.imageId, b.url, b.slug , b.title')
            ->innerJoin(GalleryImages::class, 'b', 'WITH', 'a.imageId = b.imageId')
            ->where('a.galleryId = :galleryId')
            ->setParameters([':galleryId'=>$galleryId])
            ->getQuery()->getArrayResult();
        return $data;
    }
}
