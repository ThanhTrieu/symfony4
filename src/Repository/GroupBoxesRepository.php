<?php

namespace App\Repository;

use App\Entity\GroupBoxes;
use App\Utils\Constants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupBoxes|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupBoxes|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupBoxes[]    findAll()
 * @method GroupBoxes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupBoxesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupBoxes::class);
    }

    public function getBoxByKey($key, $limit = Constants::LIMIT_TRENDING_HOME)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.boxId, a.title, a.itemJson')
            ->where('a.key = :key')
            ->setParameters([':key' => $key])
            ->setMaxResults($limit)
            ->getQuery()->getOneOrNullResult();

        return $data;
    }
}
