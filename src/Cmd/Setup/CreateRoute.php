<?php

namespace Bedrox\Cmd\Setup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRoute extends Command
{
    private const MODE_CREATE = 'create';
    private const MODE_UPDATE = 'update';

    private $routerFile;
    private $routerDir;

    /**
     * CLI configuration
     */
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
            ->addOption('router', 'r', InputOption::VALUE_OPTIONAL, $description = 'Define the router file that must be overwritten.', null)
        ;
    }

    /**
     * CLI execution
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
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
        $this->setRouterFile($input->getOption('router'));
        $success = null;
        $exists = null;
        $infosRoute = explode('::', $controller);
        $infosClass = $infosRoute[0];
        $arrayClass = explode('\\', $infosClass);
        $infosController = end($arrayClass);
        $infosFunction = $infosRoute[1];
        $infosPathRoot = realpath($_SERVER['APP']['ENTITY'] . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        $infosPath = $infosPathRoot . DIRECTORY_SEPARATOR . $infosClass . '.php';
        $output->writeln('Name (url)  : ' . $name . ' (' . $uri . ')');
        $output->writeln('Controller  : ' . $controller);
        $output->writeln('Router file : ' . $this->getRouterFile());
        $output->writeln('====================================================================================================');
        $output->writeln('Looking for existing routes and methods... ');
        $output->write('Search for an existing method... ');
        $hasMethod = $this->hasMethod($infosClass, $infosFunction);
        if (!$hasMethod) {
            $output->writeln('<fg=green;options=bold>No function found.</>');
        } else {
            $output->writeln('<fg=red;options=bold>The function already exists.</>');
        }
        $output->write('Search for an existing configuration... ');
        $hasConfig = $this->hasConfig($name, $uri);
        if (!$hasConfig) {
            $output->writeln('<fg=green;options=bold>No configuration found.</>');
        } else {
            $output->writeln('<fg=red;options=bold>The route\'s configuration already exists.</>');
        }
        $exists = ($hasMethod === false && $hasConfig === false) ? false : true;
        if (!$exists) {
            $this->createNewRouter($this->getRouterFile());
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
        } else {
            $output->writeln('<fg=red;options=bold>Please check your configuration and/or retry with new parameters.</>');
        }
        $output->writeln('====================================================================================================');
    }

    /**
     * Check if the route exists in config file
     *
     * @param string $name
     * @param string $uri
     * @return bool
     */
    private function hasConfig(string $name, string $uri): bool
    {
        $content = file_exists($this->getRouterFile()) ? file_get_contents($this->getRouterFile()) : '';
        $hasName = preg_match('/' . $name . '/i', $content);
        $hasUri = preg_match('/' . strtr($uri, '/', '\/') . '/i', $content);
        return ($hasName === 0 && $hasUri === 0) ? false : true;
    }

    /**
     * Check if method exists in controller
     *
     * @param string $infosClass
     * @param string $infosFunction
     * @return bool
     */
    private function hasMethod(string $infosClass, string $infosFunction): bool
    {
        $class = new $infosClass();
        return method_exists($class, $infosFunction);
    }

    /**
     * Create route configuration in router file
     *
     * @param string $name
     * @param string $uri
     * @param string $controller
     * @return bool
     */
    private function createRouteConfig(string $name, string $uri, string $controller): bool
    {
        $router = file_get_contents($this->getRouterFile());
        $router .= '
' . $name . ':
  path: \'' . $uri . '\'
  controller: \'' . $controller . '\'
';
        return file_put_contents($this->getRouterFile(), $router);
    }

    /**
     * Create new Controller for the route
     *
     * @param string $infosController
     * @param string $infosFunction
     * @param string $infosPath
     * @return bool
     */
    private function createRouteFunction(string $infosController, string $infosFunction, string $infosPath): bool
    {
        $content = '<?php

namespace App\Controllers;

use Bedrox\Core\Controller;
use Bedrox\Core\Render;

class ' . $infosController . ' extends Controller
{' . $this->getMethodCode($infosFunction) . '
}
';
        return file_put_contents($infosPath, $content);
    }

    /**
     * Update controller for the route
     *
     * @param string $infosFunction
     * @param string $infosPath
     * @return bool
     */
    private function updateRouteFunction(string $infosFunction, string $infosPath): bool
    {
        $content = file_get_contents($infosPath);
        $position = strripos($content, '}');
        $contentM1 = substr($content, 0, $position);
        $contentM1 .= $this->getMethodCode($infosFunction) . '
}
';
        return file_put_contents($infosPath, $contentM1);
    }

    /**
     * @param string $infosFunction
     * @return string
     */
    private function getMethodCode(string $infosFunction): string
    {
        return '
    /**
     * @return Render
     */
    public function ' . $infosFunction . '(): Render
    {
        return $this->render();
    }';
    }

    /**
     * @return string
     */
    private function getRouterFile(): string
    {
        return $this->routerFile;
    }

    /**
     * @param string|null $routerFile
     * @return CreateRoute
     */
    private function setRouterFile(?string $routerFile): self
    {
        $this->routerDir = dirname($_SERVER['APP']['ROUTER']);
        if (!empty($routerFile)) {
            $this->routerFile = $this->routerDir . DIRECTORY_SEPARATOR . $routerFile;
            $routerInfos = pathinfo($this->routerFile);
            $this->routerDir = $routerInfos['dirname'];
        } else {
            $this->routerFile = $_SERVER['APP']['ROUTER'];
        }
        return $this;
    }

    /**
     * @param string $routerFile
     */
    private function createNewRouter(string $routerFile): void
    {
        $routerInfos = pathinfo($routerFile);
        $this->routerDir = $routerInfos['dirname'];
        if (!file_exists($this->routerDir)) {
            if (mkdir($this->routerDir)) {
                if (!file_exists($routerInfos['basename'])) {
                    file_put_contents($routerFile, '');
                }
            }
        }
    }
}

