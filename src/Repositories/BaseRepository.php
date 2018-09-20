<?php

namespace Sedliarchuk\QueryBuilder\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Sedliarchuk\QueryBuilder\Filters\Type\FilterTypeManager;
use Sedliarchuk\QueryBuilder\Objects\MetaDataAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Sedliarchuk\QueryBuilder\Objects\PagerfantaBuilder;
use Sedliarchuk\QueryBuilder\Services\Pager;
use Symfony\Component\HttpFoundation\Request;

class BaseRepository extends EntityRepository
{
    protected $request;

    protected $useResultCache = false;

    /** @var FilterTypeManager */
    protected $filterTypeManager;

    protected $routeName;

    protected $currentEntityAlias;

    protected $queryOptions = [];

    protected $metadata;

    public function __construct($manager, $class)
    {
        parent::__construct($manager, $class);

        //описание репозитория
        $this->metadata = new MetaDataAdapter();
        $this->metadata->setClassMetadata($this->getClassMetadata());
        $this->metadata->setEntityName($this->getEntityName());

    }


    public function useResultCache($bool)
    {
        $this->useResultCache = $bool;
    }

    /**
     * Принимаем фильтры через запрос
     * @param Request $request
     * @return BaseRepository
     */
    public function setRequest(Request $request)
    {
        //извлекае все аттрибуты для пагинации
        foreach ($request->attributes->all() as $attributeName => $attributeValue) {
            $requestAttributes[$attributeName] = $request->attributes->get(
                $attributeName,
                $attributeValue
            );
        }

        //сохраняем запрос
        $this->request = $request;
        //сохраняем роут
        $this->setRouteName($request->attributes->get('_route'));
        //запускаем менеджера типы фильтров
        $filterTypeManager = new FilterTypeManager();
        //задаем репозиторий
        $filterTypeManager->setRepository($this);
        //менеджер типов сохраняем в переменную
        $this->setFilterTypeManager($filterTypeManager);
        //обрабатываем запрос
        $this->getFilterTypeManager()->handleRequest($request);
        //возвращаем репозиторий
        return $this;
    }

    /**
     * строим запрос к базе данных
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    function buildQuery(QueryBuilder $qb) {
        //запускаем строительство в типах фильтров
        $this->getFilterTypeManager()->buildQuery($qb);
        return $qb;
    }


    /**
     * @return MetaDataAdapter
     */
    public function getMetadata(): MetaDataAdapter
    {
        return $this->metadata;
    }

    private function convertInArray($data) {
        if ( ! is_array($data) and json_decode($data)) $data =  json_decode($data, true);

        if (! is_array($data)) return [];

        return $data;
    }


    public function getRequest()
    {
        return $this->request;
    }

    public function setRouteName($routeName = '')
    {
        $this->routeName = $routeName;
        return $this;
    }



    public function paginateResults(QueryBuilder $queryBuilder)
    {
        $ormAdapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfantaBuilder = new PagerfantaBuilder(new PagerfantaFactory(), $ormAdapter);
        $pager = new Pager();
        
        return $pager->paginateResults(
            $this->request,
            $ormAdapter,
            $pagerfantaBuilder,
            $this->routeName,
            $this->useResultCache
        );
    }

    /**
     * @return FilterTypeManager
     */
    public function getFilterTypeManager(): FilterTypeManager
    {
        return $this->filterTypeManager;
    }

    /**
     * @param FilterTypeManager $filterTypeManager
     */
    public function setFilterTypeManager(FilterTypeManager $filterTypeManager): void
    {
        $this->filterTypeManager = $filterTypeManager;
    }

    protected function getCurrentEntityAlias()
    {
        return $this->currentEntityAlias;
    }

    protected function setCurrentEntityAlias($currentEntityAlias)
    {
        $this->currentEntityAlias = $currentEntityAlias;
    }

    public function getEntityAlias()
    {
        return $this->metadata->getEntityAlias();
    }

    protected function relationship($queryBuilder)
    {
        return $queryBuilder;
    }
}
