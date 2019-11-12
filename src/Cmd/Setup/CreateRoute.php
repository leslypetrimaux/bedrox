<?php

namespace Bedrox\Cmd\Setup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRoute extends Command
{
    protected function configure()
    {
        $this
            ->setName('bedrox:router:create')
            ->setDescription('Create new Route/Controller')
            ->setHelp('Add new URI and Controller to your application')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(array(
            '==================================================',
            'Router: Create a new route & controller',
            '=================================================='
        ));
        // TODO: create route name
        // TODO: create route URI
        // TODO: create route's Controller
        // TODO: create route's parameters
        // TODO: update routes.yaml
        // TODO: create/update Controller file
        $output->writeln('==================================================');
    }
}

