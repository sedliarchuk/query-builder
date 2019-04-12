<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.04.2019
 * Time: 11:49
 */

namespace Sedliarchuk\QueryBuilder\Filters;


use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterList extends FilterAbstract
{
    const FILTER_ALIAS = 'list';

    /**
     * Строим связь с внешними таблицами
     * @param QueryBuilder $qb
     * @return \Doctrine\ORM\Query\Expr\Func
     */
    function buildQueryJoin(QueryBuilder $qb) {
        $field = $this->getField();
        $fieldName = (explode('.', $field))[0];
        //имя поля
        $field = $this->getQBAlias($qb) .'.'.$fieldName;
        //создаем линк на таблицу
        $tableAlias = mb_strcut($field, 0, 1).$this->getIntParameter();
        $qb->innerJoin($field, $tableAlias);

        $value = explode(',', $this->getValue());
        $parameterName = $fieldName.$this->getIntParameter();
        $qb->setParameter($parameterName, $value);

        if (!isset((explode('.', $this->getField()))[1])) {
            return $qb->expr()->in(
                $tableAlias.'.id', ':'.$parameterName
            );
        } else {
            return $qb->expr()->in(
                $tableAlias.'.'.(explode('.', $this->getField()))[1], ':'.$parameterName
            );
        }
    }

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);
        $field = $this->getField();
        $fieldName = (explode('.', $field))[0];

        //проверяем на наличие поле в базе данных
        if ( ! $this->issetField($fieldName)) {
            return false;
        }
        
        if ($this->isJoinField($fieldName)) {
            return $this->buildQueryJoin($qb);
        }

        $field = $this->getQBAlias($qb) .'.'.$fieldName;
        $parameterName = $fieldName.$this->getIntParameter();
        $value = explode(',', $this->getValue());

        $qb->setParameter($parameterName, $value);

        return $qb->expr()->in(
            $field, ':'.$parameterName
        );
    }
}
