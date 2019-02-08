<?php

namespace Bedrox\Core;


class QueryBuilder extends Repository
{
    /**
     * See the SGBD function for documentation.
     *
     * @param string $query
     * @return array|null
     */
    public function query(string $query): ?array
    {
        return $this->con->buildQuery($query);
    }
}