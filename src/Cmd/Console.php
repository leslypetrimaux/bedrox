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
        if (!empty($args[0])) {
            switch ($args[0]) {
                case 'generate':
                    if (!empty($args[1])) {
                        print_r('La génération est en cours de développement.');
                        $success = (new Cli())->generate($args[1], $args);
                        print_r($success);
                    } else {
                        print_r('La commande de génération demande un second argument. Nous vous invitons à consulter la documentation pour la liste des commandes.');
                    }
                    break;
                default:
                    print_r('Cette commande n\'existe pas. Nous vous invitons à consulter la documentation pour la liste des commandes.');
            }
        }
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
