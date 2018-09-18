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

class FilterGte extends FilterAbstract
{
    const FILTER_ALIAS = 'gte';

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);
        $field = $this->getField();

        //проверяем на наличие поле в базе данных
        if ( ! $this->issetField($field) or $this->isJoinField($field)) {
            return false;
        }
        $field = $this->getQBAlias($qb) .'.'.$this->getField();
        $parameterName = $this->getField().$this->getIntParameter();
        $qb->setParameter($parameterName, $this->getValue());

        return $qb->expr()->gte(
            $field, ':'.$parameterName
        );
    }
}