<?php

namespace Sedliarchuk\QueryBuilder\Filters;


class FiltersManager
{
    /**
     * @var FilterAbstract[]
     */
    private $requestFilters = [];

    public function __construct()
    {
        $this->init();
    }

    /**
     * @var FilterAbstract[]
     */
    private $filtersStorage = [];

    /**
     * @param $key
     * @return FilterAbstract|null
     */
    public function getFilterStorage($key): ?FilterAbstract
    {
        return $this->filtersStorage[$key] ?? null;
    }

    /**
     * @param FilterAbstract[] $storage
     */
    public function setFilterStorage(array $storage): void
    {
        $this->filtersStorage = $storage;
    }

    /**
     * @param $key
     * @param $filter
     */
    public function addFilter($key, $filter): void
    {
        $this->filtersStorage[$key] = $filter;
    }

    private function init(): void
    {
        $this->loadClasses();
    }

    /**
     * из массива в объект
     * @param array $data
     */
    public function handleFilter(array $data): void
    {
        if (
        !isset($data['field'], $data['data']['value'], $data['data']['type'], $this->filtersStorage[$data['data']['type']])
        ) {
            return;
        }

        //если фильтр равен дате
        if ($data['data']['type'] === FilterEq::FILTER_ALIAS && is_string($data['data']['value']) && preg_match(FilterAbstract::$dateBetweenPattern, $data['data']['value'])) {
            $className = $this->filtersStorage[FilterBetween::FILTER_ALIAS];
        } else {
            $className = $this->filtersStorage[$data['data']['type']];
        }

        /** @var FilterAbstract $filter */
        $filter = new $className();
        $filter
            ->setField($data['field'])
            ->setValue($data['data']['value']);

        $this->addRequestFilter($filter);
    }


    private function loadClasses(): void
    {
        $dirContains = scandir(__DIR__);

        foreach ($dirContains as $val) {
            if (!preg_match('~^Filter((?!Abstract|Manager|Interface)[\s\S])*$~', $val)) {
                continue;
            }

            $className = __NAMESPACE__ . '\\' . str_replace('.php', '', $val);
            /** @var FilterAbstract $obj */
            $obj = new $className();
            if (!($obj instanceof FilterAbstract)) {
                continue;
            }
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
     * @param FilterAbstract $filter
     */
    public function addRequestFilter(FilterAbstract $filter): void
    {
        $this->requestFilters[] = $filter;
    }
}