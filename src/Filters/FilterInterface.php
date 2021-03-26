<?php
namespace Sedliarchuk\QueryBuilder\Filters;

interface FilterInterface
{
    public function setMeta($meta);
    public function getMeta();
    public function setSubstitutionPattern($substitutionPattern);
    public function getSubstitutionPattern();
}