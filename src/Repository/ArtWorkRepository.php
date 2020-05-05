<?php

namespace App\Repository;

use App\Entity\ArtWork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtWork|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtWork|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtWork[]    findAll()
 * @method ArtWork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtWorkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtWork::class);
    }
}
