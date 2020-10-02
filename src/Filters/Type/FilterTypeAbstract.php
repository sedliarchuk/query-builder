<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.09.2018
 * Time: 14:22
 */

namespace Sedliarchuk\QueryBuilder\Filters\Type;


use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

abstract class FilterTypeAbstract
{
    /** @var Request */
    private $request;
    private $requestData;
    /**
     * @var FilterTypeManager
     */
    private $filterTypeManager;

    function __construct(FilterTypeManager $filterTypeManager)
    {
        $this->filterTypeManager = $filterTypeManager;
    }

    abstract function buildQuery(QueryBuilder $qb);

    /**
     * Разбираем запрос на объекты
     * @param Request|array $request
     * @return FilterTypeAbstract
     */
    function handleRequest($request) {
//        dump($request);
        $data = [];
        if ($request instanceof Request) {
            $this->setRequest($request);
            $data = $request->query->get(static::getAlias());
        } elseif (isset($request[static::getAlias()])) {
            $data = $request[static::getAlias()];
        }

        $this->requestData = $this->convertInArray($data);
    }

    /**
     * Принимаем массив или JSON строку
     * @param $data
     * @return array|mixed
     */
    private function convertInArray($data) {
        if ( ! is_array($data) and json_decode($data)) {
            $data =  json_decode($data, true);
        }
        if (! is_array($data)) {
            return [];
        }

        return $data;
    }

    function setRequest(Request $request) {
        $this->request = $request;
    }
    function getRequest() {
        return $this->request;
    }
    static function getAlias() {
        return static::FILTER_ALIAS;
    }

    /**
     * @return mixed
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * @param mixed $requestData
     */
    public function setRequestData($requestData): void
    {
        $this->requestData = $requestData;
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
}