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
            ->setName('bedrox:new:router')
            ->setAliases(['bd:n:r', 'bedrox:route'])
            ->setDescription('Create new Route/Controller')
            ->setHelp('Add new URI and Controller/function to your application')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the new Route (ex: my_route).')
            ->addArgument('uri', InputArgument::REQUIRED, 'The URI of the new Route (ex: /my/custom/path).')
            ->addArgument('controller', InputArgument::REQUIRED, 'The Controller for the new Route (ex: Namespace\Class::function).')
            ->addArgument('mode', InputArgument::OPTIONAL, 'Define the write mode : Create the Controller (create), Update existing Controller (update).', self::MODE_UPDATE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(array(
            '====================================================================================================',
            'Router: Create a new route & controller',
            '===================================================================================================='
        ));
        $name = $input->getArgument('name');
        $uri = $input->getArgument('uri');
        $controller = $input->getArgument('controller');
        $mode = $input->getArgument('mode');
        $success = null;
        $output->writeln('Name : ' . $name . ' (' . $uri . ')');
        $output->writeln($controller);
        $output->writeln('====================================================================================================');
        $infosRoute = explode('::', $controller);
        $infosClass = $infosRoute[0];
        $arrayClass = explode('\\', $infosClass);
        $infosController = end($arrayClass);
        $infosFunction = $infosRoute[1];
        $infosPathRoot = realpath($_SERVER['APP']['ENTITY'] . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        $infosPath = $infosPathRoot . DIRECTORY_SEPARATOR . $infosClass . '.php';
        switch ($mode) {
            case self::MODE_CREATE:
                $output->write('Search for an existing Controller... ');
                if (!file_exists($infosPath)) {
                    $output->writeln('<fg=cyan;options=bold>No file found.</>');
                    $output->write('Creating the Route\'s Controller and function... ');
                    $success = $this->createRouteFunction($infosController, $infosFunction, $infosPath);
                    if ($success) {
                        $output->writeln('<fg=green;options=bold>OK</>');
                    } else {
                        $output->writeln('<fg=red;options=bold>KO</>');
                    }
                } else {
                    $output->writeln('<fg=red;options=bold>File already exists.</>');
                    $output->writeln('A controller already exists... <fg=cyan;options=bold>Process will update the existing file.</>');
                    $output->write('Creating the Controller\'s function... ');
                    $success = $this->updateRouteFunction($infosFunction, $infosPath);
                    if ($success) {
                        $output->writeln('<fg=green;options=bold>OK</>');
                    } else {
                        $output->writeln('<fg=red;options=bold>KO</>');
                    }
                }
                break;
            case self::MODE_UPDATE:
            default:
                $output->write('Search for the Controller... ');
                if (file_exists($infosPath)) {
                    $output->writeln('<fg=green;options=bold>OK</>');
                    $output->write('Creating the Controller\'s function... ');
                    $success = $this->updateRouteFunction($infosFunction, $infosPath);
                    if ($success) {
                        $output->writeln('<fg=green;options=bold>OK</>');
                    } else {
                        $output->writeln('<fg=red;options=bold>KO</>');
                    }
                } else {
                    $output->writeln('<fg=red;options=bold>No file found.</>');
                    $output->writeln('No controller exists... <fg=cyan;options=bold>Process will create the new file.</>');
                    $output->write('Creating the Route\'s Controller and function... ');
                    $success = $this->createRouteFunction($infosController, $infosFunction, $infosPath);
                    if ($success) {
                        $output->writeln('<fg=green;options=bold>OK</>');
                    } else {
                        $output->writeln('<fg=red;options=bold>KO</>');
                    }
                }
                break;
        }
        if ($success) {
            $output->write('Updating the router configuration... ');
            if ($this->createRouteConfig($name, $uri, $controller)) {
                $output->writeln('<fg=green;options=bold>OK</>');
            } else {
                $output->writeln('<fg=red;options=bold>KO</>');
            }
        }
        $output->writeln('====================================================================================================');
    }

    private function createRouteConfig(string $name, string $uri, string $controller): bool
    {
        $router = file_get_contents($_SERVER['APP']['ROUTER']);
        $router .= '
' . $name . ':
  path: \'' . $uri . '\'
  controller: \'' . $controller . '\'
';
        $success = file_put_contents($_SERVER['APP']['ROUTER'], $router);
        return $success;
    }

    private function createRouteFunction(string $infosController, string $infosFunction, string $infosPath): bool
    {
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
        return $success;
    }

    private function updateRouteFunction(string $infosFunction, string $infosPath): bool
    {
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
        return $success;
    }
}

