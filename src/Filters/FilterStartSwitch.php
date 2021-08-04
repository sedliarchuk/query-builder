<?php

namespace Sedliarchuk\QueryBuilder\Filters;


use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterStartSwitch extends FilterAbstract
{
    public const FILTER_ALIAS = 'startswitch';

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);

        $fieldAlias = $this->getQbFieldAlias($qb);

        //проверяем на наличие поле в базе данных
        if (!$fieldAlias) {
            return false;
        }
        $parameterName = preg_replace('~[^A-z]~', '', $this->getField()) . $this->getIntParameter();
        $qb->setParameter($parameterName, $this->getValue().'%');

        return $qb->expr()->like(
            $fieldAlias, ':'.$parameterName
        );
    }
}