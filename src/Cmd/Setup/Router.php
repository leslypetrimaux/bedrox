<?php

namespace Bedrox\Cmd\Setup;

use Bedrox\Config\Setup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Router extends Command
{
    protected function configure()
    {
        $this
            ->setName('bedrox:router:create')
            ->setDescription('Reconfigure your Application security strategy.')
            ->setHelp('Reconfigure your application\'s encrypt algo and secret key.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(array(
            '==================================================',
            'Router: Create a new route & controller',
            '=================================================='
        ));
        // TODO:
        $output->writeln('==================================================');
    }
}

