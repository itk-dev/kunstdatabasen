<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Repository;

use App\Entity\Item;
use App\Repository\Traits\OrderByTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    use OrderByTrait;

    /**
     * ItemRepository constructor.
     *
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * Get a query with the given parameters.
     *
     * @param string|null $itemType
     * @param string|null $search
     * @param string|null $type
     * @param string|null $category
     * @param string|null $building
     * @param array       $orderBy
     *
     * @return \Doctrine\ORM\Query
     */
    public function getQuery(string $itemType = null, string $search = null, string $type = null, string $category = null, string $building = null, array $orderBy = [['purchaseDate', Criteria::DESC]]): Query
    {
        $qb = $this->createQueryBuilder('e');
        null !== $itemType && $qb->andWhere('e INSTANCE OF :itemType')->setParameter('itemType', $this->getEntityManager()->getClassMetadata($itemType));
        null !== $search && $qb->andWhere('e.name LIKE :search')->setParameter('search', '%'.$search.'%');
        null !== $type && $qb->andWhere('e.type = :type')->setParameter('type', $type);
        null !== $category && $qb->andWhere('e.category = :category')->setParameter('category', $category);
        null !== $building && $qb->andWhere('e.building = :building')->setParameter('building', $building);

        $this->addOrderBy($qb, $orderBy);

        return $qb->getQuery();
    }
}
