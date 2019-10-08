<?php


namespace Bedrox\Cmd;


class Exec
{
    /**
     * @param string $command
     * @return string
     */
    public static function exec(string $command): string
    {
        return shell_exec($command);
    }
}
