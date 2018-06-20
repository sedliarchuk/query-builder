<?php

namespace Sedliarchuk\QueryBuilder\Services;

/**
 * Class StringParser
 * @package Sedliarchuk\QueryBuilder\Services
 */
class StringParser
{
    public function numberOfTokens($string)
    {
        return count($this->exploded($string));
    }

    private function exploded($string)
    {
        return explode('_', $string);
    }

    public function tokenize($string, $position)
    {
        return $this->exploded($string)[$position];
    }


    /**
     * @param $string
     * @return string
     */
    public function camelize($string)
    {
        $camelized = $this->tokenize($string, 0);

        for ($i = 1; $i < $this->numberOfTokens($string); $i++) {
            $camelized .= ucfirst($this->tokenize($string, $i));
        }

        return $camelized;
    }
}
