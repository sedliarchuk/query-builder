<?php

namespace Sedliarchuk\QueryBuilder\Filters;


use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterGte extends FilterAbstract
{
    public const FILTER_ALIAS = 'gte';

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);

        $fieldAlias = $this->getQbFieldAlias($qb);

        //проверяем на наличие поле в базе данных
        if (!$fieldAlias) {
            return false;
        }

        $parameterName = preg_replace('~[^A-z]~', '', $this->getField()) . $this->getIntParameter();
        $qb->setParameter($parameterName, $this->getValue());

        if ($this->getValue() instanceof \DateTime && !$this->isDateTypeField($this->getField())) {
            return $qb->expr()->gte(
                'DATE('.$fieldAlias.')', ':' . $parameterName
            );
        }
        return $qb->expr()->gte(
            $fieldAlias, ':' . $parameterName
        );
    }
}