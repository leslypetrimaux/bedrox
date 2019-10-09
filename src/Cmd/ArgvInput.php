<?php


namespace Bedrox\Cmd;


class ArgvInput
{
    private $args;

    public function __construct(array $argv = null)
    {
        if (null === $argv) {
            Console::print('Erreur critique ! Aucune commande n\'a été renseignée.');
        } else {
            $this->args = $argv;
        }
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        $args = array();
        if (count($this->args) > 1) {
            foreach ($this->args as $key => $value) {
                if ($key > 0) {
                    if (preg_match('/(=)/', $value)) {
                        $arg = explode('=', $value);
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
