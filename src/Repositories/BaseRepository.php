<?php

namespace Sedliarchuk\QueryBuilder\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Sedliarchuk\QueryBuilder\Filters\Type\FilterTypeManager;
use Sedliarchuk\QueryBuilder\Objects\MetaDataAdapter;
use Sedliarchuk\QueryBuilder\Objects\PagerfantaBuilder;
use Sedliarchuk\QueryBuilder\Queries\QueryBuilderFactory;
use Sedliarchuk\QueryBuilder\Queries\QueryBuilderOptions;
use Sedliarchuk\QueryBuilder\Services\Pager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Request;

class BaseRepository extends EntityRepository
{
    protected $request;

    protected $useResultCache = true;

    /** @var FilterTypeManager */
    protected $filterTypeManager;

    protected $routeName;

    protected $currentEntityAlias;

    protected $embeddedFields;

    protected $joins = [];

    /** @var QueryBuilderFactory  */
    protected $queryBuilderFactory;

    protected $queryOptions = [];

    protected $metadata;

    public function __construct($manager, $class)
    {
        parent::__construct($manager, $class);

        //описание репозитория
        $this->metadata = new MetaDataAdapter();
        $this->metadata->setClassMetadata($this->getClassMetadata());
        $this->metadata->setEntityName($this->getEntityName());

        //собираем запрос к базе данных передаем менеджер
        $this->queryBuilderFactory = new QueryBuilderFactory($this->getEntityManager());
    }

    
    public function initFromQueryBuilderOptions(QueryBuilderOptions $options)
    {
        $this->queryBuilderFactory->createQueryBuilder(
            $this->getEntityName(),
            $this->metadata->getEntityAlias()
        );

        $this->queryBuilderFactory->loadMetadataAndOptions(
            $this->metadata,
            $options
        );
    }

    public function getQueryBuilderFactory()
    {
        $this->initFromQueryBuilderOptions($this->queryOptions);

        return $this->queryBuilderFactory;
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
        foreach ($request->attributes->all() as $attributeName => $attributeValue) {
            $requestAttributes[$attributeName] = $request->attributes->get(
                $attributeName,
                $attributeValue
            );
        }

        $this->request = $request;
        $this->setRouteName($request->attributes->get('_route'));
        $filterTypeManager = new FilterTypeManager();
        $filterTypeManager->setRepository($this);
        $this->setFilterTypeManager($filterTypeManager);
        $this->getFilterTypeManager()->handleRequest($request);
        return $this;
    }

    function buildQuery(QueryBuilder $qb) {
        $this->getFilterTypeManager()->buildQuery($qb);
        return $qb;
    }



    public function setQueryOptions(QueryBuilderOptions $options)
    {
        $this->queryOptions = $options;
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

    protected function getEmbeddedFields()
    {
        return $this->embeddedFields;
    }

    protected function setEmbeddedFields(array $embeddedFields)
    {
        $this->embeddedFields = $embeddedFields;
    }

    public function getEntityAlias()
    {
        return $this->metadata->getEntityAlias();
    }

    protected function relationship($queryBuilder)
    {
        return $queryBuilder;
    }

    public function getQueryBuilderFactoryWithoutInitialization()
    {
        return $this->queryBuilderFactory;
    }
}
