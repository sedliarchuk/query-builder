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

        $fieldAlias = $this->getQbFieldAlias($qb);

        //проверяем на наличие поле в базе данных
        if (!$fieldAlias) {
            return false;
        }

        if (is_array($this->getValue()) && count($this->getValue()) === 2) {
            $values = $this->getValue();
        } else {
            $values = explode(',', $this->getValue());
        }
        if (count($values) !== 2) {
            return false;
        }

        $start = preg_replace('~[^A-z]~', '', $this->getField()) . $this->getIntParameter();
        $end = preg_replace('~[^A-z]~', '', $this->getField()) . $this->getIntParameter();

        $qb->setParameter($start, $values[0]);
        $qb->setParameter($end, $values[1]);

        return $qb->expr()->between(
            $fieldAlias, ':' . $start, ':' . $end
        );
    }
}