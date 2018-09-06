<?php

namespace Sedliarchuk\QueryBuilder\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Sedliarchuk\QueryBuilder\Exceptions\InvalidFiltersException;
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

    protected $useResultCache = false;

    protected $routeName;

    protected $currentEntityAlias;

    protected $embeddedFields;

    protected $joins = [];

    /** @var QueryBuilderFactory  */
    protected $queryBuilderFactory;

    protected $queryOptions;

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
        return $this->setQueryOptionsFromRequest($request);
    }

    public function setRequestWithFilter(Request $request, $filter)
    {
        return $this->setQueryOptionsFromRequestWithCustomFilter($request, $filter);
    }

    public function setRequestWithOrFilter(Request $request, $orFilter)
    {
        return $this->setQueryOptionsFromRequestWithCustomOrFilter($request, $orFilter);
    }

    public function setQueryOptions(QueryBuilderOptions $options)
    {
        $this->queryOptions = $options;
    }

    /**
     * Разбираем фильтры из запроса
     * @param Request|null $request
     * @return $this
     */
    public function setQueryOptionsFromRequest(Request $request = null)
    {
        $requestAttributes = [];

        foreach ($request->attributes->all() as $attributeName => $attributeValue) {
            $requestAttributes[$attributeName] = $request->attributes->get(
                $attributeName,
                $attributeValue
            );
        }

        //получаем фильтры
        $filters     = $request->query->get('filtering', []);
        //получаем фильтры или
        $orFilters   = $request->query->get('filtering_or', []);
        //получаем фильтры системные
        $hiddenFilters   = $request->query->get('filtering_hidden', []);
        $hiddenFiltersOr = $request->query->get('filtering_hidden_or', []);
        //сортировка
        $sorting     = $request->query->get('sorting', []);
        $printing    = $request->query->get('printing', []);
        $rel         = $request->query->get('rel', '');
        //пагинация
        $page        = $request->query->get('page', '');
        //поля для вывода
        $select      = $request->query->get('select', $this->metadata->getEntityAlias());
        $filtering   = $request->query->get('filtering', []);
        $limit       = $request->query->get('limit', '');

        //если данные приходят в json то декодим
        $sorting        =  $this->convertInArray($sorting);
        $hiddenFilters  =  $this->convertInArray($hiddenFilters);
        $hiddenFiltersOr  =  $this->convertInArray($hiddenFiltersOr);
        $filters        =  $this->convertInArray($filters);
        $orFilters      =  $this->convertInArray($orFilters);
        $filtering      =  $this->convertInArray($filtering);

        $requestProperties = [
            'filtering'         => $filtering,
            'orFiltering'       => $orFilters,
            'hiddenFiltering'   => $hiddenFilters,
            'hiddenFilteringOr'   => $hiddenFiltersOr,
            'limit'             => $limit,
            'page'              => $page,
            'filters'           => $filters,
            'orFilters'         => $orFilters,
            'sorting'           => $sorting,
            'rel'               => $rel,
            'printing'          => $printing,
            'select'            => $select,
        ];

        $options = array_merge(
            $requestAttributes,
            $requestProperties
        );

        $this->queryOptions = QueryBuilderOptions::fromArray($options);

        return $this;
    }

    private function convertInArray($data) {
        if ( ! is_array($data) and json_decode($data)) $data =  json_decode($data, true);

        if (! is_array($data)) return [];

        return $data;
    }

    private function ensureFilterIsValid($filters)
    {
        if (!is_array($filters)) {

            $message = "Wrong query string exception: ";
            $message .= var_export($filters, true) . "\n";
            $message .= "Please check query string should be something like " .
                "http://127.0.0.1:8000/?filtering[status]=todo";

            throw new InvalidFiltersException($message);
        }
    }

    public function setQueryOptionsFromRequestWithCustomFilter(Request $request = null, $filter)
    {
        $filters = $request->query->get('filtering', []);
        $orFilters = $request->query->get('filtering_or', []);
        $sorting = $request->query->get('sorting', []);
        $printing = $request->query->get('printing', []);
        $rel = $request->query->get('rel', '');
        $page = $request->query->get('page', '');
        $select = $request->query->get('select', $this->metadata->getEntityAlias());
        $filtering = $request->query->get('filtering', '');
        $limit = $request->query->get('limit', '');

        if ( ! is_array($sorting) and json_decode($sorting)) $sorting =  json_decode($sorting, true);
        if ( ! is_array($filters) and json_decode($filters)) $filters =  json_decode($filters, true);
        if ( ! is_array($orFilters) and json_decode($orFilters)) $orFilters =  json_decode($orFilters, true);
        if ( ! is_array($filtering) and json_decode( $filtering)) $filtering =  json_decode($filtering, true);

        $this->ensureFilterIsValid($filters);
        $filters = array_merge($filters, $filter);

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filterValue) {
            if (is_array($filterValue)) {
                foreach ($filterValue as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal] = $internal;
                    $count += 1;
                }
            } else {
                $filterOrCorrected[$key] = $filterValue;
            }
        }

        $this->queryOptions = QueryBuilderOptions::fromArray([
            '_route' => $request->attributes->get('_route'),
            '_route_params' => $request->attributes->get('_route_params', []),
            'id' => $request->attributes->get('id'),
            'filtering' => $filtering,
            'limit' => $limit,
            'page' => $page,
            'filters' => $filters,
            'orFilters' => $filterOrCorrected,
            'sorting' => $sorting,
            'rel' => $rel,
            'printing' => $printing,
            'select' => $select,
        ]);

        return $this;
    }

    public function setQueryOptionsFromRequestWithCustomOrFilter(Request $request = null, $orFilter)
    {
        $filters = $request->query->get('filtering', []);
        $orFilters = $request->query->get('filtering_or', []);
        $sorting = $request->query->get('sorting', []);
        $printing = $request->query->get('printing', []);
        $rel = $request->query->get('rel', '');
        $page = $request->query->get('page', '');
        $select = $request->query->get('select', $this->metadata->getEntityAlias());
        $filtering = $request->query->get('filtering', '');
        $limit = $request->query->get('limit', '');

        if ( ! is_array($sorting) and json_decode($sorting)) $sorting =  json_decode($sorting, true);
        if ( ! is_array($filters) and json_decode($filters)) $filters =  json_decode($filters, true);
        if ( ! is_array($orFilters) and json_decode($orFilters)) $orFilters =  json_decode($orFilters, true);
        if ( ! is_array($filtering) and json_decode( $filtering)) $filtering =  json_decode($filtering, true);

        $orFilters = array_merge($orFilters, $orFilter);

        $filterOrCorrected = [];

        $count = 0;
        foreach ($orFilters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $keyInternal => $internal) {
                    $filterOrCorrected[$keyInternal] = $internal;
                    $count += 1;
                }
            } else {
                $filterOrCorrected[$key] = $filter;
            }
        }

        $this->queryOptions = QueryBuilderOptions::fromArray([
            '_route' => $request->attributes->get('_route'),
            '_route_params' => $request->attributes->get('_route_params', []),
            'id' => $request->attributes->get('id'),
            'filtering' => $filtering,
            'limit' => $limit,
            'page' => $page,
            'filters' => $filters,
            'orFilters' => $filterOrCorrected,
            'sorting' => $sorting,
            'rel' => $rel,
            'printing' => $printing,
            'select' => $select,
        ]);

        return $this;
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

    /**
     * Применяме все фильтры
     * @return \Hateoas\Representation\PaginatedRepresentation
     * @throws \Sedliarchuk\QueryBuilder\Component\Meta\Exceptions\UnInitializedQueryBuilderException
     * @throws \Sedliarchuk\QueryBuilder\Exceptions\MissingFieldsException
     * @throws \Sedliarchuk\QueryBuilder\Exceptions\MissingFiltersException
     */
    public function findAllPaginated()
    {
        $this->initFromQueryBuilderOptions($this->queryOptions);

        $this->queryBuilderFactory->filter();
        $this->queryBuilderFactory->sort();

        $queryBuilder = $this->queryBuilderFactory->getQueryBuilder();

        $this->lastQuery = $queryBuilder->getQuery()->getSql();
        $this->lastParameters = $queryBuilder->getQuery()->getParameters();

        return $this->paginateResults($queryBuilder);
    }

    public function getLastQuery()
    {
        return [
            'query' => $this->lastQuery,
            'params' =>  $this->lastParameters,
        ];
    }

    protected function paginateResults(QueryBuilder $queryBuilder)
    {
        $ormAdapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfantaBuilder = new PagerfantaBuilder(new PagerfantaFactory(), $ormAdapter);
        $pager = new Pager();
        return $pager->paginateResults(
            $this->queryOptions,
            $ormAdapter,
            $pagerfantaBuilder,
            $this->routeName,
            $this->useResultCache
        );
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
