<?php

namespace Sedliarchuk\QueryBuilder;

class Dictionary
{
    const DEFAULT_OPERATOR = self::OPERATOR_EQ;
    const OPERATOR_EQ = 'eq';
    const OPERATOR_NEQ = 'neq';
    const OPERATOR_GT = 'gt';
    const OPERATOR_GTE = 'gte';
    const OPERATOR_LT = 'lt';
    const OPERATOR_LTE = 'lte';
    const OPERATOR_STARTSWITH = 'startswith';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_NOTCONSAINS = 'notcontains';
    const OPERATOR_ENDSWITH = 'endswith';
    const OPERATOR_LIST = 'list';
    const OPERATOR_NLIST = 'nlist';
    const OPERATOR_FIELD_EQ = 'field_eq';
    const OPERATOR_ISNULL = 'isnull';
    const OPERATOR_ISNOTNULL = 'isnotnull';
    const OPERATOR_LISTCONTAINS = 'listcontains';
    const OPERATOR_BETWEEN = 'between';

    private static $operatorMap = [
        self::OPERATOR_EQ => [
            'meta' => '=',
        ],
        self::OPERATOR_NEQ => [
            'meta' => '!=',
        ],
        self::OPERATOR_GT => [
            'meta' => '>',
        ],
        self::OPERATOR_GTE => [
            'meta' => '>=',
        ],
        self::OPERATOR_LT => [
            'meta' => '<',
        ],
        self::OPERATOR_LTE => [
            'meta' => '<=',
        ],
        self::OPERATOR_STARTSWITH => [
            'meta' => 'LIKE',
            'substitution_pattern' => '{string}%'
        ],
        self::OPERATOR_CONTAINS => [
            'meta' => 'LIKE',
            'substitution_pattern' => '%{string}%'
        ],
        self::OPERATOR_NOTCONSAINS => [
            'meta' => 'NOT LIKE',
            'substitution_pattern' => '%{string}%'
        ],
        self::OPERATOR_ENDSWITH => [
            'meta' => 'LIKE',
            'substitution_pattern' => '%{string}'
        ],
        self::OPERATOR_LIST => [
            'meta' => 'IN',
            'substitution_pattern' => '({string})',
        ],
        self::OPERATOR_NLIST => [
            'meta' => 'NOT IN',
            'substitution_pattern' => '({string})',
        ],
        self::OPERATOR_FIELD_EQ => [
            'meta' => '=',
        ],
        self::OPERATOR_ISNULL => [
            'meta' => 'IS NULL',
        ],
        self::OPERATOR_ISNOTNULL => [
            'meta' => 'IS NOT NULL',
        ],
        self::OPERATOR_LISTCONTAINS => [
            'meta' => 'LIKE',
            'substitution_pattern' => '({string})',
        ],
        self::OPERATOR_BETWEEN => [
            'meta' => 'BETWEEN'
        ],
    ];

    public static function getOperators()
    {
        return self::$operatorMap;
    }
}

