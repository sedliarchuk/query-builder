<?php

namespace Sedliarchuk\QueryBuilder\Filters;

use DateTime;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Sedliarchuk\QueryBuilder\Repositories\BaseRepository;
use Doctrine\ORM\QueryBuilder;

class FilterAbstract implements FilterInterface
{
    public static $parameterInt = 0;
    public static $datePattern = '/^([\d]{4}-[\d]{2}-[\d]{2}|[\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2}|today|yesterday|[\d]+((minute|hour|day|week|year|month)Ago)|[\d]+(day))$/';
    public static $dateBetweenPattern = '/^(today|month|year|week|day|yesterday|hour|minute)$/';
    private $meta;
    private $substitutionPattern;
    private $field;
    private $value;
    /** @var BaseRepository */
    private $repository;


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function issetField($field): bool
    {
        $metadata = $this->repository->getMetadata()->getMetadata();

        if (!@$metadata->getReflectionProperty($field)) {
            return false;
        }
        return true;
    }


    public function isDateTypeField($fieldName):bool
    {
        $metadata = $this->repository->getMetadata()->getMetadata();

        try {
            $fieldData = $metadata->getFieldMapping($fieldName);
            if (isset($fieldData['type']) && $fieldData['type'] === 'datetime') {
                return true;
            }
        } catch (Exception $exception) {
            return false;
        }

        return false;
    }
    /**
     * Проверка на внешние таблицы
     * @param $field
     * @return bool
     */
    public function isJoinField($field): bool
    {
        $metadata = $this->repository->getMetadata()->getMetadata();
        try {
            $fieldJoin = $metadata->getAssociationMapping($field);

        } catch (MappingException $e) {
            $fieldJoin = false;
        }

        return !(!$fieldJoin or (!isset($metadata->associationMappings[$field]['joinTable']) and
                is_null($metadata->associationMappings[$field]['mappedBy'])));
    }

    /**
     * @param $input
     * @return string
     */
    public function camelCaseToUnderscore($input): string
    {
        return mb_strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $input), '_'));
    }

    public function setSubstitutionPattern($substitutionPattern): void
    {
        $this->substitutionPattern = $substitutionPattern;
    }

    public function getSubstitutionPattern()
    {
        return $this->substitutionPattern;
    }


    public static function getAlias()
    {
        return static::FILTER_ALIAS;
    }

    public function getQBAlias(QueryBuilder $qb)
    {
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
     * @return mixed
     */
    public function getQbFieldAlias(QueryBuilder $qb): ?string
    {
        $field = $this->getField();
        $fieldData = explode('.', $field);
        $fieldName = $fieldData[0];

        //проверяем на наличие поле в базе данных
        if ($this->issetField($fieldName) && !$this->isJoinField($fieldName)) {
            return $this->getQBAlias($qb) . '.' . $fieldName;
        }

        if (!$this->isJoinField($fieldName)) {
            return null;
        }

        //имя поля
        $field = $this->getQBAlias($qb) . '.' . $fieldName;
        //создаем линк на таблицу
        $tableAlias = mb_strcut($field, 0, 1) . $this->getIntParameter();
        $qb->innerJoin($field, $tableAlias);

        if (!isset($fieldData[1])) {
            return $tableAlias . '.id';
        }
        return $tableAlias . '.' . $fieldData[1];
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


    public function getIntParameter(): int
    {
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
     * @throws Exception
     */
    public function setValue($value): FilterAbstract
    {
        //работаем с датой
        if (!is_array($value) && preg_match(self::$datePattern, $value)) {
            $value = $this->convertDateValue($value);
        }
        $this->value = $value;
        return $this;
    }


    public function buildQuery(QueryBuilder $qb, BaseRepository $repository)
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getRepository(): BaseRepository
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
        $date = new DateTime();
        $key = false;
        if (preg_match('~^(yesterday|hour|minute|day|week|year|month|today)$~', $value, $res)) {
            $key = $res[0];
        } elseif (preg_match('~^[\d]{4}-[\d]{2}-[\d]{2}|[\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2}$~', $value)) {
            $date = new DateTime($value);
        }

        if (preg_match('~^(\d+)(day)$~', $value, $res)) {
            return new DateTime($res[1].' day');
        }

        if ($value === 'yesterday') {
            $date->modify('-1 day');
        } else if (preg_match('/([\d]*?)(hour|minute|day|week|year|month)(Ago)/', $value, $param)) {
            $date->modify('-' . $param[1] . ' ' . $param[2]);
        } else if (preg_match('/[\d]{4}-[\d]{2}-[\d]{2}/', $value, $param)) {
            $date = new DateTime($value);
        }

        if (get_class($this)::FILTER_ALIAS === FilterBetween::FILTER_ALIAS) {
            if (in_array($key, ['today', 'month', 'year', 'week', 'day', 'yesterday'])) {
                return [$date->format('Y-m-d 00:00:00'), $date->format('Y-m-d 23:59:59')];
            }

            if ($key === 'hour') {
                return [$date->format('Y-m-d H:00:00'), $date->format('Y-m-d H:59:59')];
            }

            if ($key === 'minute') {
                return [$date->format('Y-m-d H:i:00'), $date->format('Y-m-d H:i:59')];
            }
        }

        if (in_array($key, ['hour', 'minute'])) {
            return $date->format('Y-m-d H:i:s');
        }

        return $date;
    }
}
