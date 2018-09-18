<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.09.2018
 * Time: 14:21
 */

namespace Sedliarchuk\QueryBuilder\Filters\Type;


use Doctrine\ORM\QueryBuilder;

class FilterTypeLimit extends FilterTypeAbstract
{
    const FILTER_ALIAS = 'limit';

    function buildQuery(QueryBuilder $qb)
    {
        // TODO: Implement buildQuery() method.
    }
}