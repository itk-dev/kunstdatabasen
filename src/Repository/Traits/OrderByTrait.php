<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

/**
 * Trait OrderByTrait.
 */
trait OrderByTrait
{
    /**
     * Add order by to query builder.
     *
     * @param QueryBuilder $qb
     * @param array        $orderBy list of field, direction pairs
     */
    protected function addOrderBy(QueryBuilder $qb, array $orderBy)
    {
        foreach ($orderBy as $order) {
            $qb->orderBy($qb->getRootAliases()[0].'.'.$order[0], $order[1]);
        }
    }
}
