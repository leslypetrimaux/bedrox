<?php

namespace Bedrox\Cmd;

use App\Kernel;
use Bedrox\Cmd\Setup\CreateRoute;
use Bedrox\Cmd\Setup\ReconfigureSecurityStrategy;
use Bedrox\Core\Controller;
use Bedrox\Core\Env;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

class Console
{

    public static function run(): void
    {
        // Loading Environment
        (new Env(true))->load(Env::FILE_ENV_ROOT);
        // Loading Doctrine Console Application
        try {
            $em = (new Controller())->getDoctrine();
            $con = $em->getConnection();
            $con->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $helperSet = new HelperSet(array(
                'db' => new ConnectionHelper($con),
                'em' => new EntityManagerHelper($em)
            ));
            $cli = new Application('Bedrox Command Line Interface', 'alpha-dev');
            $cli->setCatchExceptions(true);
            $cli->setHelperSet($helperSet);
            ConsoleRunner::addCommands($cli);
            $commands = array(
                new ReconfigureSecurityStrategy(), // bedrox:configure:security - Configure the security strategy
                new CreateRoute(), // bedrox:router:create - Create routes
            );
            $cli->addCommands($commands);
            $cli->addCommands(self::addCommands()); // Import user's custom commands
            $cli->run();
        } catch (Exception $e) {
            self::print('The following error just append : (' . $e->getCode() . ') ' . $e->getMessage());
        }
    }

    public static function addCommands(): array
    {
        return Kernel::getCustomCmd();
    }

    public static function print(string $text = '', bool $eol = true): void
    {
        $newLine = $eol ? PHP_EOL : '';
        print_r($text . $newLine);
    }
}
