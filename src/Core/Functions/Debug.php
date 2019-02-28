<?php

use Bedrox\Core\Functions\Dumper;

if (!function_exists('dump')) {
    /**
     * @param mixed ...$strings
     */
    function dump(...$strings)
    {
        Dumper::dump($strings);
    }
}

if (!function_exists('dd')) {
    /**
     * @param mixed ...$strings
     */
    function dd(...$strings)
    {
        Dumper::dump($strings);
        Dumper::printAndDie();
    }
}