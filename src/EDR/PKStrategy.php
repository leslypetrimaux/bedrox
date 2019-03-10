<?php

namespace Bedrox\EDR;

class PKStrategy
{
    public $strategy;

    /**
     * Table constructor.
     *
     * @param array $strategy
     */
    public function __construct(array &$strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @return array|null
     */
    public function getStrategy(): ?array
    {
        return $this->strategy;
    }
}