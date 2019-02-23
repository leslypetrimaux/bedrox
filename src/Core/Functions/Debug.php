<?php

use Bedrox\Core\Functions\Dumper;

if (!function_exists('dump')) {
    /**
     * @param mixed ...$strings
     */
    function dump(...$strings)
    {
        foreach ($strings as $string) {
            Dumper::dump($string);
        }
    }
}

if (!function_exists('dd')) {
    /**
     * @param mixed ...$strings
     */
    function dd(...$strings)
    {
        foreach ($strings as $string) {
            Dumper::dump($string);
        }
        die;
    }
}