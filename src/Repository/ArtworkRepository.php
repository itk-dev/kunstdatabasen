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
     * @param string|null $artistGender
     *
     * @return \Doctrine\ORM\Query
     */
    public function getQuery(string $search = null, string $type = null, string $category = null, string $building = null, int $yearFrom = null, int $yearTo = null, int $minWidth = null, int $maxWidth = null, int $minHeight = null, int $maxHeight = null, string $artistGender = null): Query
    {
        $qb = $this->createQueryBuilder('e');

        if (null !== $search) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('e.name', ':search'),
                $qb->expr()->like('e.artSerial', ':search'),
                $qb->expr()->like('e.artist', ':search')
            ));
            $qb->setParameter('search', '%'.$search.'%');
        }

        null !== $type && $qb->andWhere('e.type = :type')->setParameter('type', $type);
        null !== $category && $qb->andWhere('e.category = :category')->setParameter('category', $category);
        null !== $building && $qb->andWhere('e.building = :building')->setParameter('building', $building);
        null !== $yearFrom && $qb->andWhere('e.productionYear >= :yearFrom')->setParameter('yearFrom', $yearFrom);
        null !== $yearTo && $qb->andWhere('e.productionYear <= :yearTo')->setParameter('yearTo', $yearTo);
        null !== $minWidth && $qb->andWhere('e.width >= :minWidth')->setParameter('minWidth', $minWidth);
        null !== $maxWidth && $qb->andWhere('e.width <= :maxWidth')->setParameter('maxWidth', $maxWidth);
        null !== $minHeight && $qb->andWhere('e.height >= :minHeight')->setParameter('minHeight', $minHeight);
        null !== $maxHeight && $qb->andWhere('e.height <= :maxHeight')->setParameter('maxHeight', $maxHeight);
        null !== $artistGender && $qb->andWhere('e.artistGender = :artistGender')->setParameter('artistGender', $artistGender);

        return $qb->getQuery();
    }
}
