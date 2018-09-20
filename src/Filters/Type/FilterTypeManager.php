<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.09.2018
 * Time: 14:34
 */

namespace Sedliarchuk\QueryBuilder\Filters\Type;


use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class FilterTypeManager
{
    /** @var BaseRepository */
    private $repository;
    function __construct()
    {
        $this->init();
    }

    /**
     * @var FilterTypeAbstract[]
     */
    private $filtersTypeStorage = [];

    /**
     * @return FilterTypeAbstract[]
     */
    public function getFiltersStorage()
    {
        return $this->filtersTypeStorage;
    }

    /**
     * @param FilterTypeAbstract[] $storage
     */
    public function setFilterStorage($storage)
    {
        $this->filtersTypeStorage = $storage;
    }

    /**
     * @param Request $request
     */
    public function handleRequest(Request $request) {
        foreach ($this->getFiltersStorage() as $item) {
            $item->handleRequest($request);
        }
    }

    /**
     * @param FilterTypeAbstract $filter
     */
    public function addFilter(FilterTypeAbstract $filter)
    {
        $this->filtersTypeStorage[$filter->getAlias()] = $filter;
    }

    private function init() {
        $this->loadClasses();
    }

    /**
     * Подгружаем скрипты из папки
     */
    private function loadClasses() {
        $dirContains = scandir(dirname(__FILE__));

        foreach ($dirContains as $val) {
            if (!preg_match('~^FilterType((?!Abstract|Manager|Interface)[\s\S])*$~', $val)) continue;
            $className = __NAMESPACE__.'\\'.str_replace('.php', '', $val);
            $obj = new $className($this);
            if ( ! ($obj instanceof FilterTypeAbstract)) continue;
            $this->addFilter($obj);
        }
    }


    /**
     * Строим запрос к базе данных
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    function buildQuery(QueryBuilder $qb) {
        //перебираем типы фильтрова
        foreach ($this->getFiltersStorage() as $filterType) {
            //строим запрос
            $filterType->buildQuery($qb);
        }
        return $qb;
    }


    /**
     * @return BaseRepository
     */
    public function getRepository(): BaseRepository
    {
        return $this->repository;
    }

    /**
     * @param BaseRepository $repository
     */
    public function setRepository(BaseRepository $repository): void
    {
        $this->repository = $repository;
    }
}