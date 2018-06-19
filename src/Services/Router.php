<?php

namespace Sedliarchuk\QueryBuilder\Services;

use Hateoas\Configuration\Route;
use Sedliarchuk\QueryBuilder\Queries\QueryBuilderOptions;

class Router
{
    public function createRouter(QueryBuilderOptions $queryOptions, $routeName) :Route
    {
        $params = [];
        $routeParams = [];

        if (null != $queryOptions->get('_route_params')) {
            $routeParams = array_keys($queryOptions->get('_route_params'));
        }

        $list = array_merge([
            'filtering',
            'limit',
            'page',
            'sorting',
        ], $routeParams);

        foreach ($list as $itemKey => $itemValue) {
            $params[$itemValue] = $queryOptions->get($itemValue);
        }

        if (!isset($routeName)) {
            $routeName = $queryOptions->get('_route');
        }

        return new Route($routeName, $params);
    }
}