<?php

namespace Bedrox\Cmd;

class Console
{
    /**
     * @param array $argv
     */
    public static function run(array $argv): void
    {
        $args = self::getArguments($argv);
        self::executeArguments($args);
    }

    public static function executeArguments(array &$args): void
    {
        var_dump($args);
    }

    /**
     * @param array $argv
     * @return array
     */
    public static function getArguments(array &$argv): array
    {
        $args = array();
        if (count($argv) > 1) {
            foreach ($argv as $key => $value) {
                if ($key > 0) {
                    if (preg_match('/(=)/', $value)) {
                        $arg = explode('=', $value);
                        $args[$arg[0]] = $arg[1];
                    } else if (preg_match('/(:)/', $value)) {
                        $arg = explode(':', $value);
                        $args[$arg[0]] = $arg[1];
                    } else {
                        $args[] = $value;
                    }
                }
            }
        }
        return $args;
    }
}
