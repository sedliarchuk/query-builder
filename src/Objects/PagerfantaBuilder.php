<?php

namespace Sedliarchuk\QueryBuilder\Objects;

use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Representation\PaginatedRepresentation;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class PagerfantaBuilder
{
    private $pagerfantaFactory;

    private $ormAdapter;

    public function __construct(PagerfantaFactory $pagerfantaFactory, DoctrineORMAdapter $ormAdapter)
    {
        $this->pagerfantaFactory = $pagerfantaFactory;
        $this->ormAdapter = $ormAdapter;
    }

    public function create($limit, $page) :Pagerfanta
    {
        $pager = new Pagerfanta($this->ormAdapter);
        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function createRepresentation($route, $limit, $page): PaginatedRepresentation
    {
        $pager = $this->create(
            $limit,
            $page
        );

        return $this->pagerfantaFactory->createRepresentation($pager, $route);
    }
}
