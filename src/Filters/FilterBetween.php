<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.09.2018
 * Time: 11:49
 */

namespace Sedliarchuk\QueryBuilder\Filters;


use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterBetween extends FilterAbstract
{
    const FILTER_ALIAS = 'between';

    /**
     * @param QueryBuilder $qb
     * @return bool|\Doctrine\ORM\Query\Expr\Func
     */
    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);
        $field = $this->getField();

        //проверяем на наличие поле в базе данных
        if ( ! $this->issetField($field) or $this->isJoinField($field)) {
            return false;
        }

        $field = $this->getQBAlias($qb) .'.'.$this->getField();
        $values = explode(',', $this->getValue());
        if (count($values) != 2) return false;

        $start = $this->getField().$this->getIntParameter();
        $end = $this->getField().$this->getIntParameter();

        $qb->setParameter($start ,$values[0]);
        $qb->setParameter($end ,$values[1]);

        return $qb->expr()->between(
            $field, ':'.$start, ':'.$end
        );
    }
}