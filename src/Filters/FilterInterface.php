<?php
/**
 * Created by PhpStorm.
 * User: sedliarchuk
 * Date: 12.09.2018
 * Time: 11:39
 */
namespace Sedliarchuk\QueryBuilder\Filters;

interface FilterInterface
{
    public function setMeta($meta);
    public function getMeta();
    public function setSubstitutionPattern($substitutionPattern);
    public function getSubstitutionPattern();
}