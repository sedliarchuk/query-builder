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

class FilterList extends FilterAbstract
{
    const FILTER_ALIAS = 'list';
    



    public function checkField() {

    }

    function joinTable(QueryBuilder $qb, BaseRepository $repository) {

    }

    function buildQueryJoin(QueryBuilder $qb) {
        $field = $this->getQBAlias($qb) .'.'.$this->getField();
        $tableAlias = mb_strcut($field, 0, 1).$this->getIntParameter();
        $qb->innerJoin($field, $tableAlias);

        $value = explode(',', $this->getValue());
        $parameterName = $this->getField().$this->getIntParameter();
        $qb->setParameter($parameterName, $value);

        return $qb->expr()->in(
            $tableAlias.'.id', ':'.$parameterName
        );
    }

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);
        $field = $this->getField();

        //проверяем на наличие поле в базе данных
        if ( ! $this->issetField($field)) {
            return false;
        }
        
        if ($this->isJoinField($field)) {
            return $this->buildQueryJoin($qb);
        }

        $field = $this->getQBAlias($qb) .'.'.$this->getField();
        $parameterName = $this->getField().$this->getIntParameter();
        $value = explode(',', $this->getValue());

        $qb->setParameter($parameterName, $value);

        return $qb->expr()->in(
            $field, ':'.$parameterName
        );
    }
}