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
        $field = $this->getField();

        //проверяем на наличие поле в базе данных
        if (!$this->issetField($field) || $this->isJoinField($field)) {
            return false;
        }
        $field = $this->getQBAlias($qb) . '.' . $this->getField();
        $parameterName = $this->getField() . $this->getIntParameter();
        $value = explode(',', $this->getValue());

        $qb->setParameter($parameterName, $value);

        return $qb->expr()->notIn(
            $field, ':' . $parameterName
        );
    }
}