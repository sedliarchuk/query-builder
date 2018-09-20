<?php

namespace Sedliarchuk\QueryBuilder\Objects;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;

class MetaDataAdapter
{
    /** @var ClassMetadata */
    private $metadata;

    private $entityName;

    public function setClassMetadata(ClassMetadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    public function getFields()
    {
        return array_keys($this->metadata->fieldMappings);
    }

    public function getEntityAlias()
    {
        $entityName = explode('\\', strtolower($this->entityName));

        $entityName = $entityName[count($entityName) - 1][0];

        return $entityName[0];
    }

    function issetField($field) {
        $metadata = $this->metadata;

        if ( ! @$metadata->getReflectionProperty($field)) {
            return false;
        }
        return true;
    }

    function isJoinField($field) {
        $metadata = $this->metadata;
        try {
            $fieldJoin = $metadata->getAssociationMapping($field);
        } catch (MappingException $e) {
            $fieldJoin = false;
        }
        if ( ! $fieldJoin or !isset($metadata->associationMappings[$field]['joinTable'])) {
            return false;
        }
        return true;
    }

    /**
     * @return ClassMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
