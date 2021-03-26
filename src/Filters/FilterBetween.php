<?php

namespace Sedliarchuk\QueryBuilder\Filters;


use Doctrine\ORM\Query\Expr\Func;
use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterBetween extends FilterAbstract
{
    public const FILTER_ALIAS = 'between';

    /**
     * @param QueryBuilder $qb
     * @param BaseRepository $repository
     * @return bool|Func
     */
    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);
        $field = $this->getField();

        //проверяем на наличие поле в базе данных
        if (!$this->issetField($field) || $this->isJoinField($field)) {
            return false;
        }

        $field = $this->getQBAlias($qb) . '.' . $this->getField();
        if (is_array($this->getValue()) && count($this->getValue()) === 2) {
            $values = $this->getValue();
        } else {
            $values = explode(',', $this->getValue());
        }
        if (count($values) !== 2) {
            return false;
        }

        $start = $this->getField() . $this->getIntParameter();
        $end = $this->getField() . $this->getIntParameter();

        $qb->setParameter($start, $values[0]);
        $qb->setParameter($end, $values[1]);

        return $qb->expr()->between(
            $field, ':' . $start, ':' . $end
        );
    }
}