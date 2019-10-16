<?php

namespace Bedrox\Cmd;

use Bedrox\Cmd\Setup\Security;
use Bedrox\Core\Controller;
use Bedrox\Core\Env;
use Bedrox\Core\Response;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

class Console
{
    protected const CMD_GENERATE = 'generate';
    protected const CMD_CONF = 'configure';
    protected const CMD_DOCTRINE = 'doctrine';

    public static function run(): void
    {
        // Loading Environment
        (new Env(true))->load(Env::FILE_ENV_ROOT);
        // Loading Doctrine Console Application
        try {
            $em = (new Controller(new Response()))->getDoctrine();
            $helperSet = new HelperSet(array(
                'db' => new ConnectionHelper($em->getConnection()),
                'em' => new EntityManagerHelper($em)
            ));
            $cli = new Application('Bedrox Command Line Interface', $_SERVER['APP']['VERSION']);
            $cli->setCatchExceptions(true);
            $cli->setHelperSet($helperSet);
            ConsoleRunner::addCommands($cli);
            $commands = array(
                new Security(), // Configure the security strategy
            );
            $cli->addCommands($commands);
            $cli->run();
        } catch (Exception $e) {
            self::print('L\'erreur suivante vient de se produire : (' . $e->getCode() . ') ' . $e->getMessage());
        }
    }

    public static function print(string $text = '', bool $eol = true): void
    {
        $newLine = $eol ? PHP_EOL : '';
        print_r($text . $newLine);
    }
}
