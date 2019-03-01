<?php

namespace Bedrox\Core;

use Bedrox\Core\Annotations\AnnotationsTypes;
use Bedrox\Core\Annotations\PhpParser;
use Bedrox\Core\Interfaces\iEntityManager;

class EntityManager implements iEntityManager
{
    protected $phpParser;
    protected $annotationsTypes;
    protected $response;

    /**
     * EntityManager Constructor
     */
    public function __construct()
    {
        $this->phpParser = new PhpParser();
        $this->annotationsTypes = new AnnotationsTypes('@Database');
        $this->response = new Response();
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
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_EM_REPO',
                'message' => 'Impossible de récupérer un Repository de l\'Application.'
            )));
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
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_EM_ENTITY',
                'message' => 'La classe "' . $entity . '" n\'existe pas.'
            )));
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
        $table = $this->phpParser->getAnnotationValue($this->annotationsTypes->dbTable, $matches);
        return $table;
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
        $primary = $this->phpParser->getAnnotationValue($this->annotationsTypes->dbPrimaryKey, $matches);
        return $primary;
    }

    public function getTableKeyStrategy(Entity $entity): array
    {
        $properties = $this->phpParser->classProperties($entity);
        return $this->phpParser->getStrategyFromProperties($properties);
    }

    /**
     * Return all columns used in the table/Entity.
     *
     * @param Entity $entity
     * @return array|null
     */
    public function getColumns(Entity $entity): array
    {
        $properties = $this->phpParser->classProperties($entity);
        return $this->phpParser->getColumnsFromProperties($properties);
    }
}