<?php

namespace Bedrox\Core\Interfaces;

use Bedrox\Core\Entity;

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
     * @param int $id
     * @return Entity|mixed|null
     */
    public function find(string $table, int $id): ?Entity;

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