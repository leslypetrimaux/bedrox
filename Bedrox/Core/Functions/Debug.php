<?php

/**
 * @param mixed ...$strings
 */
function dump(...$strings)
{
    foreach ($strings as $string) {
        var_dump($string);
    }
}

/**
 * @param mixed ...$strings
 */
function dd(...$strings)
{
    foreach ($strings as $string) {
        var_dump($string);
    }
    die;
}