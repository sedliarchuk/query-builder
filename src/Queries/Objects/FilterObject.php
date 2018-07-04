<?php

namespace Sedliarchuk\QueryBuilder\Queries\Objects;

use Sedliarchuk\QueryBuilder\Services\StringParser;
use Sedliarchuk\QueryBuilder\Dictionary;

/** @since class available since release 2.2 */
final class FilterObject
{
    const FIELD = 0;

    const OPERATOR = 1;

    private $rawFilter;

    private $fieldName;

    private $operatorName;

    private $value;

    private function __construct($rawFilter)
    {
        $this->setRawFilter($rawFilter);
        $explodedRawFilter = $rawFilter;

        if (!isset($explodedRawFilter['data']['type'])) {
            $explodedRawFilter['data']['type'] = Dictionary::DEFAULT_OPERATOR;
        }

        $fieldName = $explodedRawFilter['field'];
        $this->value = $explodedRawFilter['data']['value'];
        $parser = new StringParser();
        $this->fieldName = $parser->camelize($fieldName);

        $this->operatorName = $explodedRawFilter['data']['type'];
    }

    /**
     * @param string $filter
     * @return FilterObject
     */
    public static function fromRawFilter($filter)
    {
        return new self($filter);
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getOperatorName()
    {
        return $this->operatorName;
    }

    public function isListType()
    {
        return $this->getOperatorName() == 'list'
            || $this->getOperatorName() == 'nlist';
    }

    public function isBetweenType()
    {
        return $this->getOperatorName() == 'between';
    }

    public function isFieldEqualityType()
    {
        return $this->getOperatorName() == 'field_eq';
    }

    public function getOperatorMeta()
    {
        return Dictionary::getOperators()[$this->getOperatorName()]['meta'];
    }

    public function haveOperatorSubstitutionPattern()
    {
        $operator = Dictionary::getOperators()[$this->getOperatorName()];

        return isset($operator['substitution_pattern']);
    }

    public function getOperatorsSubstitutionPattern()
    {
        $operator = Dictionary::getOperators()[$this->getOperatorName()];

        return $operator['substitution_pattern'];
    }

    public function setRawFilter($rawFilter)
    {
        $this->rawFilter = http_build_query($rawFilter);
    }

    public function getRawFilter()
    {
        return $this->rawFilter;
    }

    public function getOperator()
    {
        return $this->operatorName;
    }

    public function isNullType()
    {
        return $this->getOperatorName() === 'isnull' || $this->getOperatorName() === 'isnotnull';
    }

    public function isListContainsType()
    {
        return $this->getOperatorName() === 'listcontains';
    }
}
