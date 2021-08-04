<?php

namespace Sedliarchuk\QueryBuilder\Filters;


use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterNlist extends FilterAbstract
{
    public const FILTER_ALIAS = 'nlist';

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);
        $fieldAlias = $this->getQbFieldAlias($qb);

        //проверяем на наличие поле в базе данных
        if (!$fieldAlias) {
            return false;
        }
        $parameterName = preg_replace('~[^A-z]~', '', $this->getField()) . $this->getIntParameter();
        $value = explode(',', $this->getValue());
        $qb->setParameter($parameterName, $value);

        return $qb->expr()->notIn(
            $fieldAlias, ':' . $parameterName
        );
    }
}