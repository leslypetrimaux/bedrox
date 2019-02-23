<?php

namespace Bedrox\Core\Functions;

class Dumper
{
    /**
     * @param $var
     */
    public static function dump($var)
    {
        // TODO: Get class/line source
        var_dump($var);
    }
}