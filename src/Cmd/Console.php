<?php

namespace Bedrox\Cmd;

use Bedrox\Config\Setup;
use Bedrox\Core\Controller;
use Bedrox\Core\Env;
use Bedrox\Core\Response;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Exception;
use Symfony\Component\Console\Helper\HelperSet;

class Console
{
    protected const CMD_GENERATE = 'generate';
    protected const CMD_CONF = 'configure';
    protected const CMD_DOCTRINE = 'doctrine';

    /**
     * @param array $args
     */
    public static function run(array $args): void
    {
        // Loading Environment
        (new Env(true))->load(Env::FILE_ENV_ROOT);
        self::executeArguments($args);
    }

    public static function executeArguments(array &$args): void
    {
        if (!empty($args[0])) {
            switch ($args[0]) {
                case self::CMD_GENERATE:
                    if (!empty($args[1])) {
                        self::print('La génération est en cours de développement.');
                        $success = (new Cli())->generate($args[1], $args);
                        self::print(strval($success));
                    } else {
                        self::print('La commande de génération demande un second argument. Nous vous invitons à consulter la documentation pour la liste des commandes.');
                    }
                    break;
                case self::CMD_CONF:
                    if (!empty($args[1])) {
                        self::print('La configuration est en cours de développement.');
                        Setup::setSecurity();
                    } else {
                        self::print('La commande de génération demande un second argument. Nous vous invitons à consulter la documentation pour la liste des commandes.');
                    }
                    break;
                case self::CMD_DOCTRINE:
                    self::print('La console Doctrine n\'est pas encore disponible...');
                    try {
                        $em = (new Controller(new Response()))->getDoctrine();
                        $helperSet = new HelperSet(array(
                            'db' => new ConnectionHelper($em->getConnection()),
                            'em' => new EntityManagerHelper($em)
                        ));
                        $cli = ConsoleRunner::createApplication($helperSet);
                        $cli->run();
                    } catch (Exception $e) {
                        self::print('L\'erreur ' . $e->getCode() . ' Doctrine suivante vient de se produire : ' . $e->getMessage());
                    }
                    break;
                default:
                    self::print('Cette commande n\'existe pas. Nous vous invitons à consulter la documentation pour la liste des commandes.');
                    break;
            }
        }
    }

    public static function print(string $text = '', bool $eol = true): void
    {
        $newLine = $eol ? PHP_EOL : '';
        print_r($text . $newLine);
    }
}
