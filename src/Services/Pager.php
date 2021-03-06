<?php

namespace Sedliarchuk\QueryBuilder\Services;

use Hateoas\Representation\PaginatedRepresentation;
use Sedliarchuk\QueryBuilder\Objects\PagerfantaBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Request;

class Pager
{
    private const DEFAULT_LIMIT = 10;

    private const DEFAULT_PAGE = 1;

    private const DEFAULT_LIFETIME = 600;

    /** @var Router */
    private $router;

    public function __construct()
    {
        $this->setRouter(new Router());
    }

    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    public function paginateResults(
        Request $request,
        DoctrineORMAdapter $ormAdapter,
        PagerfantaBuilder $pagerfantaBuilder,
        $routeName,
        $useResultCache
    ): PaginatedRepresentation
    {
        $limit = $request->query->get('limit', self::DEFAULT_LIMIT);
        $page = $request->query->get('page', self::DEFAULT_PAGE);

        $query = $ormAdapter->getQuery();
        if (isset($useResultCache) && $useResultCache) {
            $query->useResultCache(true, self::DEFAULT_LIFETIME);
        }

        $route = $this->router->createRouter($request, $routeName);

        return $pagerfantaBuilder->createRepresentation($route, $limit, $page);
    }
}