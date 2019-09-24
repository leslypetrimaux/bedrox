<?php

namespace Bedrox\Core;

use Bedrox\Core\Annotations\AnnotationsTypes;
use Bedrox\Core\Annotations\PhpParser;
use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Core\Interfaces\iEntityManager;
use Bedrox\EDR;

class EntityManager implements iEntityManager
{
    protected $phpParser;
    protected $annotationsTypes;

    /**
     * EntityManager Constructor
     */
    public function __construct()
    {
        $this->phpParser = new PhpParser();
        $this->annotationsTypes = new AnnotationsTypes('@Database');
    }

    /**
     * Return Repository to query a table.
     *
     * @param string $entity
     * @return Repository
     */
    public function getRepo(?string $entity): Repository
    {
        if (!$entity) {
            BedroxException::render(
                'ERR_EM_REPO',
                'Impossible de récupérer un Repository de l\'Application.'
            );
        }
        $table = $this->getTable($this->getEntity($entity));
        return new Repository($table);
    }

    /**
     * Return an empty Entity from the name.
     *
     * @param string $entity
     * @return Entity
     */
    public function getEntity(?string $entity): Entity
    {
        $entity = 'App\Entity\\' . ucwords($entity);
        if (!class_exists($entity)) {
            BedroxException::render(
                'ERR_EM_ENTITY',
                'La classe "' . $entity . '" n\'existe pas.'
            );
        }
        return new $entity();
    }

    /**
     * Return the table used by an Entity.
     *
     * @param Entity $entity
     * @return string|null
     */
    public function getTable(Entity $entity): ?string
    {
        $document = $this->phpParser->classComment($entity);
        $matches = $this->phpParser->matchesAnnotations($document);
        $result = $this->phpParser->getAnnotationValue($this->annotationsTypes->dbTable, $matches);
        $table  = preg_replace('/\(\"|\"\)/', '', str_replace(AnnotationsTypes::DB_TABLE, '', $result));
        return (new EDR\Table($table))->getTable();
    }

    /**
     * Return the primary key of an Entity.
     *
     * @param Entity $entity
     * @return string|null
     */
    public function getTableKey(Entity $entity): ?string
    {
        $document = $this->phpParser->classComment($entity);
        $matches = $this->phpParser->matchesAnnotations($document);
        $result = $this->phpParser->getAnnotationValue($this->annotationsTypes->dbPrimaryKey, $matches);
        $values  = preg_replace('/\(\"|\"\)/', '', str_replace(AnnotationsTypes::DB_PRIMARY_KEY, '', $result));
        return (new EDR\PrimaryKeys($values))->getKeys();
    }

    /**
     * Return the foreign key of an Entity.
     *
     * @param Entity $entity
     * @return array
     */
    public function getForeignKey(Entity $entity): array
    {
        $properties = $this->phpParser->classProperties($entity);
        $results = $this->phpParser->getFKeysFromProperties($properties);
        $columns = array();
        foreach ($results as $key => $value) {
            if (!empty($value)) {
                $value  = preg_replace('/\(\"|\"\)/', '', $value);
                $columns[$key] = $this->getEntity((new EDR\ForeignKeys($value))->getEntity());
            }
        }
        return $columns;
    }

    /**
     * Returns PK strategy
     *
     * @param Entity $entity
     * @return array|null
     */
    public function getTableKeyStrategy(Entity $entity): ?array
    {
        $properties = $this->phpParser->classProperties($entity);
        $result = $this->phpParser->getStrategyFromProperties($properties);
        $value  = preg_replace('/\(\"|\"\)/', '', str_replace(AnnotationsTypes::DB_STRATEGY, '', $result));
        return (new EDR\PKStrategy($value))->getStrategy();
    }

    /**
     * Return all columns used in the table/Entity.
     *
     * @param Entity $entity
     * @return array
     */
    public function getColumns(Entity $entity): array
    {
        $properties = $this->phpParser->classProperties($entity);
        $results = $this->phpParser->getColumnsFromProperties($properties);
        $columns = array();
        foreach ($results as $key => $value) {
            $columns[$key] = new EDR\Column($value[0], $value[1], $value[2]);
        }
        return $columns;
    }
}
