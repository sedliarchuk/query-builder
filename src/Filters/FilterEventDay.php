<?php

namespace Sedliarchuk\QueryBuilder\Filters;

use DateTime;
use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterEventDay extends FilterAbstract
{
    public const FILTER_ALIAS = 'event_day';


    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        $this->setRepository($repository);

        $fieldAlias = $this->getQbFieldAlias($qb);

        //проверяем на наличие поле в базе данных
        if (!$fieldAlias) {
            return false;
        }
        $parameterName = preg_replace('~[^A-z]~', '', $this->getField()) . $this->getIntParameter();
        if (!$this->getValue() instanceof DateTime) {
            return null;
        }
        $qb->setParameter($parameterName.'Day', $this->getValue()->format('j'));
        $qb->setParameter($parameterName.'Month', $this->getValue()->format('n'));

        return $qb->expr()->andX(
            $qb->expr()->eq('DAY('.$fieldAlias.')', ':' . $parameterName.'Day'),
            $qb->expr()->eq('MONTH('.$fieldAlias.')', ':' . $parameterName.'Month')
        );
    }
}
