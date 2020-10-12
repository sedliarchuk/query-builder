<?php
namespace Sedliarchuk\QueryBuilder\Filters;

use Doctrine\ORM\Mapping\MappingException;
use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterAbstract implements FilterInterface
{
    static $parameterInt = 0;
    private $meta;
    private $substitutionPattern;
    private $field;
    private $value;
    /** @var BaseRepository */
    private $repository;


    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    function issetField($field) {
        $metadata = $this->repository->getMetadata()->getMetadata();

        if ( ! @$metadata->getReflectionProperty($field)) {
            return false;
        }
        return true;
    }

    /**
     * Проверка на внешние таблицы
     * @param $field
     * @return bool
     */
    function isJoinField($field) {
        $metadata = $this->repository->getMetadata()->getMetadata();
        try {
            $fieldJoin = $metadata->getAssociationMapping($field);

        } catch (MappingException $e) {
            $fieldJoin = false;
        }

        if ( ! $fieldJoin or (!isset($metadata->associationMappings[$field]['joinTable']) and
                is_null($metadata->associationMappings[$field]['mappedBy']))) {
            return false;
        }
        return true;
    }

    /**
     * @param $input
     * @return string
     */
    function camelCaseToUnderscore($input)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $input)), '_');
    }

    public function setSubstitutionPattern($substitutionPattern)
    {
        $this->substitutionPattern = $substitutionPattern;
    }

    public function getSubstitutionPattern()
    {
        return $this->substitutionPattern;
    }


    static function getAlias() {
        return static::FILTER_ALIAS;
    }

    function getQBAlias(QueryBuilder $qb) {
        return current($qb->getDQLPart('from'))->getAlias();
    }


    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     * @return FilterAbstract
     */
    public function setField($field): FilterAbstract
    {
        $this->field = $field;
        return $this;
    }


    public function getIntParameter() {
        self::$parameterInt++;
        return self::$parameterInt;
    }
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return FilterAbstract
     */
    public function setValue($value): FilterAbstract
    {
        //работаем с датой
        if (preg_match('/^([0-9]{4}-[0-9]{2}-[0-9]{2}|today|yesterday|[0-9]+((day|week|year|month)Ago))$/', $value))
        {
            $value = $this->convertDateValue($value);
        }
        $this->value = $value;
        return $this;
    }


    function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param mixed $repository
     */
    public function setRepository($repository): void
    {
        $this->repository = $repository;
    }

    private function convertDateValue($value)
    {
        $date = new \DateTime();
        if ($value == 'yesterday') {
            $date->modify('-1 day');
        } else if (preg_match('/([0-9])+(day|week|year|month)+(Ago)/', $value, $param)) {
            $date->modify('-'.$param[1].' '.$param[2]);
        } else if (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value, $param)) {
            $date = new \DateTime($value);
        }
        return $date->format('Y-m-d 00:00:00');
    }
}
