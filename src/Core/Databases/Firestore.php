<?php

namespace Bedrox\Core\Databases;

use Bedrox\Core\Entity;
use Bedrox\Core\EntityManager;
use Bedrox\Core\Interfaces\iSgbd;
use Bedrox\Core\Response;
use Bedrox\Google\Firebase\CloudFirestore;

class Firestore extends CloudFirestore implements iSgbd
{
    public const UTF8 = 'utf-8';

    protected $em;
    protected $response;

    /**
     * Firestore constructor.
     *
     * @param string $host
     * @param string $apiKey
     * @param string $clientId
     * @param string $oAuthToken
     * @param string $type
     */
    public function __construct(string &$host, string $apiKey, string $clientId, string $oAuthToken, string $type = 'public')
    {
        parent::__construct($host, $apiKey, $clientId, $oAuthToken, $type);
        $this->em = new EntityManager();
        $this->response = new Response();
    }

    /**
     * Get Encodage type (PDO::MYSQL_ATTR_INIT_COMMAND)
     *
     * @param string $encodage
     * @return string|null
     */
    public function getEncodage(string $encodage): ?string
    {
        return !empty($encodage) ? $encodage : self::UTF8;
    }

    /**
     * A customized query builder for FirebaseDatabase Cloud Firestore
     *
     * @param string $query
     * @return array|null
     */
    public function buildQuery(string $query): ?array
    {
        // TODO: Implement buildQuery() method.
        http_response_code(500);
        exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
            'code' => 'ERR_FIRESTORE_QUERYBUILDER',
            'message' => 'Le "QueryBuilder" pour Firebase Cloud Firestore n\'est pas encore disponible.'
        )));
    }

    /**
     * @param string $table
     * @param string $id
     * @return Entity|null
     */
    public function find(string $table, string $id): ?Entity
    {
        $path = $table . '/' . $id;
        $content = $this->get($path);
        $entity = $this->em->getEntity($table);
        $columns = $this->em->getColumns($entity);
        if ($content !== null) {
            foreach ($content as $key => $value) {
                $var = array_search($key, $columns, true);
                $entity->$var = $value;
            }
        } else {
            $entity = null;
        }
        return $entity;
    }

    /**
     * @param string $table
     * @return array|null
     */
    public function findAll(string $table): ?array
    {
        $content = $this->get($table);
        $result = array();
        foreach ($content as $col) {
            $entity = $this->em->getEntity($table);
            $columns = $this->em->getColumns($entity);
            foreach ($col as $key => $value) {
                $var = array_search($key, $columns, true);
                $entity->$var = $value;
            }
            $result[] = $entity;
        }
        return $result;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function persist(Entity $entity): bool
    {
        return $entity->getId() !== null ? $this->update($entity) : $this->insert($entity);
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function insert(Entity $entity): bool
    {
        $entity->setId(uniqid('', true));
        $data = json_encode($entity);
        $table = $this->em->getTable($entity);
        $path = $table . '/' . $entity->getId();
        return !empty($this->patch($path, $data)) ? true : false;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function update(Entity $entity): bool
    {
        $data = json_encode($entity);
        $table = $this->em->getTable($entity);
        $path = $table . '/' . $entity->getId();
        return !empty($this->patch($path, $data)) ? true : false;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function delete(Entity $entity): bool
    {
        if ($entity->getId() !== null) {
            $table = $this->em->getTable($entity);
            $path = $table . '/' . $entity->getId();
            return !empty($this->unset($path)) ? true : false;
        }
        return false;
    }
}