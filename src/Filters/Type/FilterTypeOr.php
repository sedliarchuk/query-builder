<?php

namespace Sedliarchuk\QueryBuilder\Filters\Type;


use Doctrine\ORM\QueryBuilder;
use Sedliarchuk\QueryBuilder\Filters\FiltersManager;
use Symfony\Component\HttpFoundation\Request;

class FilterTypeOr extends FilterTypeAbstract
{
    const FILTER_ALIAS = 'filtering_or';
    const OPERATOR = 'OR';

    /** @var FiltersManager  */
    private $filterManager;

    function __construct(FilterTypeManager $filterTypeManager)
    {
        parent::__construct($filterTypeManager);
        $this->filterManager = new FiltersManager();
    }

    function handleRequest($request)
    {
        parent::handleRequest($request);
        $this->handleFilters();
    }

    function handleFilters() {
        foreach ($this->getRequestData() as $data) {
            $this->filterManager->handleFilter($data);
        }
    }

    /**
     * @return FiltersManager
     */
    public function getFilterManager()
    {
        return $this->filterManager;
    }

    /**
     * @param FiltersManager $filterManager
     */
    public function setFilterManager($filterManager)
    {
        $this->filterManager = $filterManager;
    }

    function getRequestFilter() {
        return $this->getFilterManager()->getRequestFilters();
    }

    function buildQuery(QueryBuilder $qb)
    {
        if ( ! $this->getFilterManager()->getRequestFilters()) return $qb;
        $qbAnd = $qb->expr()->orX();
        foreach ($this->getFilterManager()->getRequestFilters() as $filter) {
            if ( ! $query = $filter->buildQuery($qb, $this->getFilterTypeManager()->getRepository())) continue;
            $qbAnd->add($query);
            $query = null;
        }

        $qb->andWhere($qbAnd);
        return $qb;

    }
}