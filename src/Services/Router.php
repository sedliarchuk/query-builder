<?php

namespace Sedliarchuk\QueryBuilder\Services;

use Hateoas\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class Router
{
    public function createRouter(Request $request, $routeName): Route
    {
        $params = [];
        $routeParams = [];

        if (null !== $request->attributes->get('_route_params')) {
            $routeParams = array_keys($request->attributes->get('_route_params'));
        }

        $list = array_merge([
            'filtering',
            'limit',
            'page',
            'sorting',
        ], $routeParams);

        foreach ($list as $itemKey => $itemValue) {
            $params[$itemValue] = $request->query->get($itemValue);
        }

        if (!isset($routeName)) {
            $routeName = $request->attributes->get('_route');
        }

        return new Route($routeName, $params);
    }
}