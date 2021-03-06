<?php

namespace Sedliarchuk\QueryBuilder\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Representation\PaginatedRepresentation;
use InvalidArgumentException;
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


    public function useResultCache($bool): void
    {
        $this->useResultCache = $bool;
    }

    /**
     * Принимаем фильтры через запрос
     * @param Request|array $request
     * @return BaseRepository
     */
    public function setRequest($request): BaseRepository
    {
        if (!is_array($request) && !$request instanceof Request) {
            throw new InvalidArgumentException('$request must be a array or Request object.');
        }
        //запускаем менеджера типы фильтров
        $filterTypeManager = new FilterTypeManager();
        //задаем репозиторий
        $filterTypeManager->setRepository($this);
        //менеджер типов сохраняем в переменную
        $this->setFilterTypeManager($filterTypeManager);
        //возвращаем репозиторий
        if ($request instanceof Request) {
            //сохраняем запрос
            $this->request = $request;
            //сохраняем роут
            $this->setRouteName($request->attributes->get('_route'));
        }
        //обрабатываем запрос
        $this->getFilterTypeManager()->handleRequest($request);
        return $this;
    }

    /**
     * строим запрос к базе данных
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    public function buildQuery(QueryBuilder $qb): QueryBuilder
    {
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

    private function convertInArray($data): array
    {
        if (!is_array($data) && json_decode($data, true)) {
            $data = json_decode($data, true);
        }

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }


    public function getRequest()
    {
        return $this->request;
    }

    public function setRouteName($routeName = ''): BaseRepository
    {
        $this->routeName = $routeName;
        return $this;
    }


    public function paginateResults(QueryBuilder $queryBuilder): PaginatedRepresentation
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

    protected function setCurrentEntityAlias($currentEntityAlias): void
    {
        $this->currentEntityAlias = $currentEntityAlias;
    }

    public function getEntityAlias(): string
    {
        return $this->metadata->getEntityAlias();
    }

    protected function relationship($queryBuilder)
    {
        return $queryBuilder;
    }
}
