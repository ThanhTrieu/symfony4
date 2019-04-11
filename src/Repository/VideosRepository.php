<?php

namespace App\Repository;

use App\Entity\Videos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Videos|null find($id, $lockMode = null, $lockVersion = null)
 * @method Videos|null findOneBy(array $criteria, array $orderBy = null)
 * @method Videos[]    findAll()
 * @method Videos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideosRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Videos::class);
    }

    /**
     * get Galleries Videos Count
     * author: TrieuNT
     * create date: 2018-10-31 09:30 AM
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getVideosGalleriesCount()
    {
        $count = $this->createQueryBuilder('a')
            ->select('count(a.videoId)')
            ->where('a.status = :status')
            ->setParameters([':status' => 1])
            ->getQuery()->getSingleScalarResult();
        return $count;
    }

    /**
     * get Galleries Videos Paging
     * author: TrieuNT
     * create date: 2018-10-31 09:30 AM
     * @param $pageIndex
     * @param $pageSize
     * @return array
     */
    public function getVideoGalleriesPaging($pageIndex, $pageSize)
    {
        $startIndex = ($pageIndex - 1) * $pageSize;
        $data = $this->createQueryBuilder('a')
            ->select('a.videoId,a.title,a.createdDate,a.avatar,a.url,a.description,a.creatorId')
            ->where('a.status = :status')
            ->setParameters([':status' => 1])
            ->orderBy('a.createdDate', 'DESC')
            ->setFirstResult($startIndex)
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();
        return $data;
    }

    /**
     * get detail video
     * author: TrieuNT
     * create date: 2018-11-02 10:31 AM
     * @param $id
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function getDataVideoById($id)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.videoId,a.title,a.createdDate,a.avatar,a.url,a.description,a.creatorId')
            ->where('a.videoId = :videoId')
            ->andWhere('a.status = :status')
            ->setParameters([':videoId' => $id, ':status' => 1])
            ->getQuery()
            ->getOneOrNullResult();
        return $data;
    }

    /**
     * get older video with video detail
     * author: TrieuNT
     * create date: 2018-11-02 10:36 AM
     * @param $pageIndex
     * @param $pageSize
     * @param $id
     * @return array
     */
    public function getOlderVideo($pageIndex, $pageSize, $id)
    {
        $startIndex = ($pageIndex - 1) * $pageSize;
        $data = $this->createQueryBuilder('a')
            ->select('a.videoId,a.title,a.createdDate,a.avatar,a.url,a.description,a.creatorId')
            ->where('a.status = :status')
            ->setParameters([':status' => 1])
            ->orderBy('a.createdDate', 'DESC')
            ->setFirstResult($startIndex)
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();
        return $data;
    }

    /**
     * author: TrieuNT
     * create date: 2018-11-02 11:37 AM
     * get most view video home
     * @param $pageIndex
     * @param $pageSize
     * @return array
     */
    public function getMostViewVideos($pageIndex, $pageSize)
    {
        $startIndex = ($pageIndex - 1) * $pageSize;
        $data = $this->createQueryBuilder('a')
            ->select('a.videoId,a.title,a.createdDate,a.avatar,a.url,a.description,a.creatorId')
            ->where('a.status = :status')
            ->setParameters([':status' => 1])
            ->orderBy('a.createdDate', 'DESC')
            ->setFirstResult($startIndex)
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();
        return $data;
    }
}
