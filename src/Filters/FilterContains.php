<?php

namespace Sedliarchuk\QueryBuilder\Filters;


use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterContains extends FilterAbstract
{
    public const FILTER_ALIAS = 'contains';

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);
        $field = $this->getField();

        //проверяем на наличие поле в базе данных
        if (!$this->issetField($field) || $this->isJoinField($field)) {
            return false;
        }
        $field = $this->getQBAlias($qb) . '.' . $this->getField();
        $parameterName = $this->getField() . $this->getIntParameter();
        $qb->setParameter($parameterName, '%' . $this->getValue() . '%');

        return $qb->expr()->like(
            $field, ':' . $parameterName
        );
    }
}