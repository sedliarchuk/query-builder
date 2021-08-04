<?php

namespace Sedliarchuk\QueryBuilder\Filters;


use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterIsNull extends FilterAbstract
{
    public const FILTER_ALIAS = 'isnull';

    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);
        $fieldAlias = $this->getQbFieldAlias($qb);

        //проверяем на наличие поле в базе данных
        if (!$fieldAlias) {
            return false;
        }

        return $qb->expr()->isNull(
            $fieldAlias
        );
    }
}