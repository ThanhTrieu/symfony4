<?php

namespace App\Repository;

use App\Entity\Tags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Tags|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tags|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tags[]    findAll()
 * @method Tags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Tags::class);
    }

    /**
     * Get tag by slug
     * author: ThanhDT
     * date:   2018-07-12 01:32 PM
     * @param $slug
     * @return null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTagBySlug($slug)
    {
        $tag = $this->createQueryBuilder('c')
            ->select(['c.tagId', 'c.name', 'c.slug','c.description'])
            ->where('c.slug = :slug')
            ->setParameters([':slug' => $slug])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $tag;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-24 01:53 PM
     */

    public function getAllTags()
    {
        $tag = $this->createQueryBuilder('c')
            ->select('c.tagId', 'c.name', 'c.slug')
            ->getQuery()->getResult();
        return $tag;
    }
}
