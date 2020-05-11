<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Repository;

use App\Entity\Artwork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Artwork|null find($id, $lockMode = null, $lockVersion = null)
 * @method Artwork|null findOneBy(array $criteria, array $orderBy = null)
 * @method Artwork[]    findAll()
 * @method Artwork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtworkRepository extends ServiceEntityRepository
{
    /**
     * ArtworkRepository constructor.
     *
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artwork::class);
    }

    /**
     * Get a query with the given parameters.
     *
     * @param string|null $search
     * @param string|null $type
     * @param string|null $category
     * @param string|null $building
     * @param int|null $yearFrom
     * @param int|null $yearTo
     * @param int|null $minWidth
     * @param int|null $maxWidth
     * @param int|null $minHeight
     * @param int|null $maxHeight
     *
     * @return \Doctrine\ORM\Query
     */
    public function getQuery(string $search = NULL, string $type = NULL, string $category = NULL, string $building = NULL, int $yearFrom = NULL, int $yearTo = NULL, int $minWidth = NULL, int $maxWidth = NULL, int $minHeight = NULL, int $maxHeight = NULL): Query
    {
        $qb = $this->createQueryBuilder('e');
        $search !== null && $qb->andWhere('e.name LIKE :search')->setParameter('search', '%'.$search.'%');
//        $type !== null && $qb->andWhere('e.type = :type')->setParameter('type', $type);
//        $category !== null && $qb->andWhere('e.category = :category')->setParameter('category', $category);
//        $building !== null && $qb->andWhere('e.building = :building')->setParameter('building', $building);
        $yearFrom !== null && $qb->andWhere('e.productionYear >= :yearFrom')->setParameter('yearFrom', $yearFrom);
        $yearTo !== null && $qb->andWhere('e.productionYear <= :yearTo')->setParameter('yearTo', $yearTo);

        return $qb->getQuery();
    }
}
