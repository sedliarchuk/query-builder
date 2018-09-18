<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.09.2018
 * Time: 14:21
 */

namespace Sedliarchuk\QueryBuilder\Filters\Type;


use Doctrine\ORM\QueryBuilder;

class FilterTypeSort extends FilterTypeAbstract
{
    const FILTER_ALIAS = 'sorting';

    function getQBAlias(QueryBuilder $qb) {
        return current($qb->getDQLPart('from'))->getAlias();
    }

    function buildQuery(QueryBuilder $qb)
    {
       foreach ($this->getRequestData() as $field => $sort) {
           $field = $this->getQBAlias($qb) .'.'.$field;
           $qb->addOrderBy($field, $sort);
       }

    }
}