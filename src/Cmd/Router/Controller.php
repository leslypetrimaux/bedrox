<?php


namespace Bedrox\Cmd\Router;


use Bedrox\Cmd\Console;

class Controller
{
    public const SRC = 'src';
    public const FUNCTION = 'function';

    public $src;
    public $function;

    public function __construct(string $params)
    {
        if (preg_match('/(::)/', $params)) {
            $arg = explode('::', $params);
            $this->src = $arg[0];
            $this->function = $arg[1];
        } else {
            Console::print('Merci de renseigner un nom de controller ainsi qu\'une fonction.');
        }
    }
}
