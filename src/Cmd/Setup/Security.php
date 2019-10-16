<?php

namespace Bedrox\Cmd\Setup;

use Bedrox\Config\Setup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Security extends Command
{
    protected function configure()
    {
        $this
            ->setName('bedrox:configure:security')
            ->setDescription('Reconfigure your Application security strategy.')
            ->setHelp('Description d\'aide')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Reconfiguration de la stratégie de sécurité...');
        Setup::setSecurity();
    }
}
