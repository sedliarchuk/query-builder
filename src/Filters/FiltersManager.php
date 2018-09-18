<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.09.2018
 * Time: 12:15
 */

namespace Sedliarchuk\QueryBuilder\Filters;
use Sedliarchuk\QueryBuilder\Filters\FilterAbstract;


class FiltersManager
{
    /**
     * @var FilterAbstract[]
     */
    private $requestFilters = [];

    function __construct()
    {
        $this->init();
    }

    /**
     * @var FilterAbstract[]
     */
    private $filtersStorage = [];

    /**
     * @return FilterAbstract[]
     */
    public function getFilterStorage($key)
    {
        return $this->filtersStorage;
    }

    /**
     * @param FilterAbstract[] $storage
     */
    public function setFilterStorage($storage)
    {
        $this->filtersStorage = $storage;
    }

    /**
     * @param FilterAbstract[] $storage
     */
    public function addFilter($key, $filter)
    {
        $this->filtersStorage[$key] = $filter;
    }

    private function init() {
        $this->loadClasses();
    }

    /**
     * из массива в объект
     * @param array $data
     */
    public function handleFilter(array $data) {
        if ( ! isset($data['field'])) return;
        if ( ! isset($data['data'])) return;
        if ( ! isset($data['data']['value'])) return;
        if ( ! isset($data['data']['type'])) return;
        if ( ! isset($this->filtersStorage[$data['data']['type']])) return;

        $className = $this->filtersStorage[$data['data']['type']];
        /** @var FilterAbstract $filter */
        $filter = new $className();
        $filter
            ->setField($data['field'])
            ->setValue($data['data']['value'])
        ;
        $this->addRequestFilter($filter);
    }


    private function loadClasses() {
        $dirContains = scandir(dirname(__FILE__));

        foreach ($dirContains as $val) {
            if (!preg_match('~^Filter((?!Abstract|Manager|Interface)[\s\S])*$~', $val)) continue;

            $className = __NAMESPACE__.'\\'.str_replace('.php', '', $val);
            /** @var FilterAbstract $obj */
            $obj = new $className();
            if ( ! ($obj instanceof FilterAbstract)) continue;
            $this->addFilter($obj::getAlias(), $className);
        }
    }

    /**
     * @return FilterAbstract[]
     */
    public function getRequestFilters(): array
    {
        return $this->requestFilters;
    }

    /**
     * @param FilterAbstract[] $requestFilters
     */
    public function setRequestFilters(array $requestFilters): void
    {
        $this->requestFilters = $requestFilters;
    }

    /**
     * @param FilterAbstract[] $requestFilters
     */
    public function addRequestFilter(FilterAbstract $filter)
    {
        $this->requestFilters[] = $filter;
    }
}