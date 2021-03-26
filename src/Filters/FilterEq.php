<?php

namespace Sedliarchuk\QueryBuilder\Filters;


use Doctrine\ORM\Query\Expr\Func;
use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterEq extends FilterAbstract
{
    public const FILTER_ALIAS = 'eq';

    public function buildQueryJoin(QueryBuilder $qb): Func
    {

        $field = $this->getField();
        $fieldName = (explode('.', $field))[0];
        $field = $this->getQBAlias($qb) . '.' . $fieldName;
        $tableAlias = mb_strcut($field, 0, 1) . $this->getIntParameter();
        $qb->innerJoin($field, $tableAlias);

        $value = explode(',', $this->getValue());
        $parameterName = $fieldName . $this->getIntParameter();
        $qb->setParameter($parameterName, $value);

        if (!isset((explode('.', $this->getField()))[1])) {
            return $qb->expr()->in(
                $tableAlias . '.id', ':' . $parameterName
            );
        }

        return $qb->expr()->in(
            $tableAlias . '.' . (explode('.', $this->getField()))[1], ':' . $parameterName
        );

    }

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);

        $field = $this->getField();
        $fieldName = (explode('.', $field))[0];

        //проверяем на наличие поле в базе данных
        if (!$this->issetField($fieldName)) {
            return false;
        }

        if ($this->isJoinField($fieldName)) {
            return $this->buildQueryJoin($qb);
        }


        $field = $this->getQBAlias($qb) . '.' . $fieldName;
        $parameterName = $fieldName . $this->getIntParameter();
        $qb->setParameter($parameterName, $this->getValue());

        return $qb->expr()->eq(
            $field, ':' . $parameterName
        );
    }
}
