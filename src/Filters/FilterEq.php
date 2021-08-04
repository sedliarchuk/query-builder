<?php

namespace Sedliarchuk\QueryBuilder\Filters;


use Doctrine\ORM\Query\Expr\Func;
use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterEq extends FilterAbstract
{
    public const FILTER_ALIAS = 'eq';


    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);

        $fieldAlias = $this->getQbFieldAlias($qb);

        //проверяем на наличие поле в базе данных
        if (!$fieldAlias) {
            return false;
        }
        $parameterName = preg_replace('~[^A-z]~', '', $this->getField()) . $this->getIntParameter();
        $qb->setParameter($parameterName, $this->getValue());

        return $qb->expr()->eq(
            $fieldAlias, ':' . $parameterName
        );
    }
}
