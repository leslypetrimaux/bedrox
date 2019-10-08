<?php

namespace Bedrox\EDR\Interfaces;

use Bedrox\EDR\Entity;

interface iSgbd
{

    /**
     * @param string $encodage
     * @return string|null
     */
    public function getEncodage(string $encodage): ?string;

    /**
     * @param string $query
     * @return array|null
     */
    public function buildQuery(string $query): ?array;

    /**
     * @param string $table
     * @param string $id
     * @return Entity|mixed|null
     */
    public function find(string $table, string $id): ?Entity;

    /**
     * @param string $table
     * @param array $criteria
     * @return Entity|null
     */
    public function findOneBy(string $table, array $criteria): ?Entity;

    /**
     * @param string $table
     * @return array|null
     */
    public function findAll(string $table): ?array;

    /**
     * @param Entity $entity
     * @return bool
     */
    public function persist(Entity $entity): bool;

    /**
     * @param Entity $entity
     * @return bool
     */
    public function insert(Entity $entity): bool;

    /**
     * @param Entity $entity
     * @return bool
     */
    public function update(Entity $entity): bool;

    /**
     * @param Entity $entity
     * @return bool
     */
    public function delete(Entity $entity): bool;
}
