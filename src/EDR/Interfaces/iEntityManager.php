<?php

namespace Bedrox\EDR\Interfaces;

use Bedrox\EDR\Entity;
use Bedrox\EDR\Repository;

interface iEntityManager
{
    /**
     * @param string $entity
     * @return Repository|null
     */
    public function getRepo(?string $entity): ?Repository;

    /**
     * @param string $entity
     * @return Entity|mixed
     */
    public function getEntity(?string $entity): Entity;

    /**
     * @param Entity $entity
     * @return string|null
     */
    public function getTable(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string|null
     */
    public function getTableKey(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return array
     */
    public function getForeignKey(Entity $entity): array;

    /**
     * @param Entity $entity
     * @return array|null
     */
    public function getTableKeyStrategy(Entity $entity): ?array;

    /**
     * @param Entity $entity
     * @return array|null
     */
    public function getColumns(Entity $entity): array;
}
