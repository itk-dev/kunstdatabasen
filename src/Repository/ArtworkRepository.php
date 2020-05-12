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
     * @param int|null    $yearFrom
     * @param int|null    $yearTo
     * @param int|null    $minWidth
     * @param int|null    $maxWidth
     * @param int|null    $minHeight
     * @param int|null    $maxHeight
     *
     * @return \Doctrine\ORM\Query
     */
    public function getQuery(string $search = null, string $type = null, string $category = null, string $building = null, int $yearFrom = null, int $yearTo = null, int $minWidth = null, int $maxWidth = null, int $minHeight = null, int $maxHeight = null): Query
    {
        $qb = $this->createQueryBuilder('e');
        null !== $search && $qb->andWhere('e.name LIKE :search')->setParameter('search', '%'.$search.'%');
        null !== $type && $qb->andWhere('e.type = :type')->setParameter('type', $type);
        null !== $category && $qb->andWhere('e.category = :category')->setParameter('category', $category);
        null !== $building && $qb->andWhere('e.building = :building')->setParameter('building', $building);
        null !== $yearFrom && $qb->andWhere('e.productionYear >= :yearFrom')->setParameter('yearFrom', $yearFrom);
        null !== $yearTo && $qb->andWhere('e.productionYear <= :yearTo')->setParameter('yearTo', $yearTo);

        return $qb->getQuery();
    }
}
