<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.09.2018
 * Time: 14:21
 */

namespace Sedliarchuk\QueryBuilder\Filters\Type;


use Doctrine\ORM\QueryBuilder;
use Sedliarchuk\QueryBuilder\Filters\FiltersManager;
use Symfony\Component\HttpFoundation\Request;

class FilterTypeAnd extends FilterTypeAbstract
{
    const FILTER_ALIAS = 'filtering';
    const OPERATOR = 'AND';
    /** @var FiltersManager  */
    private $filterManager;

    function __construct(FilterTypeManager $filterTypeManager)
    {
        parent::__construct($filterTypeManager);
        $this->filterManager = new FiltersManager();
    }

    function handleRequest(Request $request)
    {
        parent::handleRequest($request);
        $this->handleFilters();
    }

    function handleFilters() {
        foreach ($this->getRequestData() as $data) {
            $this->filterManager->handleFilter($data);
        }
    }

    /**
     * @return FiltersManager
     */
    public function getFilterManager()
    {
        return $this->filterManager;
    }

    /**
     * @param FiltersManager $filterManager
     */
    public function setFilterManager($filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    function buildQuery(QueryBuilder $qb)
    {
        //если нет фильтров в данном типе пропускаем
        if ( ! $this->getFilterManager()->getRequestFilters()) return $qb;
        //метка на проверку есть ли фитльтры для обработки
        $isAdd = false;

        //создаем пул запросов с условиями типа
        $qbAnd = $qb->expr()->andX();
        //извлекаем фильтра
        foreach ($this->getFilterManager()->getRequestFilters() as $filter) {
            //если по каким то причинам фильтр не собрал запрос пропускаем
            if ( ! $query = $filter->buildQuery($qb, $this->getFilterTypeManager()->getRepository())) continue;
            //ставим метку что есть запрос
            $isAdd = true;
            //добавляем запрос
            $qbAnd->add($query);
            //обнуляем запрос
            $query = null;
        }

        //если есть фильтры для запроса то записываем в общий запрос
        if ($isAdd) $qb->andWhere($qbAnd);
        //возвращаем билдер для дальнейшей работы
        return $qb;

    }
}