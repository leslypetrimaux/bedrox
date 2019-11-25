<?php

namespace Bedrox\Cmd\Setup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRoute extends Command
{
    private const MODE_CREATE = 'create';
    private const MODE_UPDATE = 'update';

    protected function configure()
    {
        $this
            ->setName('bedrox:router:create')
            ->setDescription('Create new Route/Controller')
            ->setHelp('Add new URI and Controller to your application')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the new Route (ex: my_route).')
            ->addArgument('uri', InputArgument::REQUIRED, 'The URI of the new Route (ex: /my/custom/path).')
            ->addArgument('controller', InputArgument::REQUIRED, 'The Controller for the new Route (ex: Namespace\Class::function).')
            ->addArgument('mode', InputArgument::OPTIONAL, 'Define the write mode : Create the Controller (create), Update existing Controller (update).', self::MODE_UPDATE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(array(
            '==================================================',
            'Router: Create a new route & controller',
            '=================================================='
        ));
        $name = $input->getArgument('name');
        $uri = $input->getArgument('uri');
        $controller = $input->getArgument('controller');
        $mode = $input->getArgument('mode');
        $success = false;
        $output->writeln('Name : ' . $name . ' (' . $uri . ')');
        $output->writeln($controller);
        $output->writeln('==================================================');
        $infosRoute = explode('::', $controller);
        $infosClass = $infosRoute[0];
        $arrayClass = explode('\\', $infosClass);
        $infosController = end($arrayClass);
        $infosFunction = $infosRoute[1];
        $infosPathRoot = realpath($_SERVER['APP']['ENTITY'] . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        $infosPath = $infosPathRoot . DIRECTORY_SEPARATOR . $infosClass . '.php';
        switch ($mode) {
            case self::MODE_CREATE:
                $output->write('Creating the Route\'s Controller and function... ');
                $content = '<?php

namespace App\Controllers;

use Bedrox\Core\Controller;
use Bedrox\Core\Render;

class ' . $infosController . ' extends Controller
{
    /**
     * @return Render
     */
    public function ' . $infosFunction . '(): Render
    {
        return new Render([
            \'this\' => $this
        ]);
    }
}
';
                $success = file_put_contents($infosPath, $content);
                if ($success) {
                    $output->writeln('OK');
                } else {
                    $output->writeln('KO');
                }
                break;
            case self::MODE_UPDATE:
            default:
                $output->write('Search for the Controller... ');
                if (file_exists($infosPath)) {
                    $output->writeln('OK');
                    $output->write('Creating the Route\'s function... ');
                    $content = file_get_contents($infosPath);
                    $position = strripos($content, '}');
                    $contentM1 = substr($content, 0, $position);
                    $contentM1 .= '
    /**
     * @return Render
     */
    public function ' . $infosFunction . '(): Render
    {
        return new Render([
            \'this\' => $this
        ]);
    }
}';
                    $success = file_put_contents($infosPath, $contentM1);
                    if ($success) {
                        $output->writeln('OK');
                    } else {
                        $output->writeln('KO');
                    }
                } else {
                    $output->writeln('The file "' . $infosPath . '" does not exists.');
                }
                break;
        }
        if ($success) {
            $output->write('Updating the router configuration... ');
            $router = file_get_contents($_SERVER['APP']['ROUTER']);
            $router .= '
' . $name . ':
  path: \'' . $uri . '\'
  controller: \'' . $controller . '\'
';
            if (file_put_contents($_SERVER['APP']['ROUTER'], $router)) {
                $output->writeln('OK');
            } else {
                $output->writeln('KO');
            }
        }
        $output->writeln('==================================================');
    }
}

