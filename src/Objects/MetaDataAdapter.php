<?php

namespace Sedliarchuk\QueryBuilder\Objects;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;

class MetaDataAdapter
{
    /** @var ClassMetadata */
    private $metadata;

    private $entityName;

    public function setClassMetadata(ClassMetadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function setEntityName($entityName): void
    {
        $this->entityName = $entityName;
    }

    public function getFields(): array
    {
        return array_keys($this->metadata->fieldMappings);
    }

    public function getEntityAlias(): string
    {
        $entityName = explode('\\', strtolower($this->entityName));

        $entityName = $entityName[count($entityName) - 1][0];

        return $entityName[0];
    }

    public function issetField($field): bool
    {
        $metadata = $this->metadata;

        if (!@$metadata->getReflectionProperty($field)) {
            return false;
        }
        return true;
    }

    public function isJoinField($field): bool
    {
        $metadata = $this->metadata;
        try {
            $fieldJoin = $metadata->getAssociationMapping($field);
        } catch (MappingException $e) {
            $fieldJoin = false;
        }
        return !(!$fieldJoin or !isset($metadata->associationMappings[$field]['joinTable']));
    }

    /**
     * @return ClassMetadata
     */
    public function getMetadata(): ClassMetadata
    {
        return $this->metadata;
    }
}
