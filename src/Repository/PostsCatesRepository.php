<?php

namespace App\Repository;

/**
 * author: TrieuNT
 * create date: 2018-10-24 10:35 AM
 */

use App\Entity\PostsCates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PostsCates|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostsCates|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostsCates[]    findAll()
 * @method PostsCates[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostsCatesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PostsCates::class);
    }
}
