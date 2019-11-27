<?php

namespace Bedrox\Cmd\Setup;

use Bedrox\Config\Setup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReconfigureSecurityStrategy extends Command
{
    protected function configure()
    {
        $this
            ->setName('bedrox:configure:security')
            ->setAliases(['bd:c:s', 'bedrox:config-secu'])
            ->setDescription('Reconfigure your Application security strategy.')
            ->setHelp('Reconfigure your application\'s encrypt algo and secret key.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(array(
            '==================================================',
            'Security: Secret key & encrypt algo Configuration',
            '=================================================='
        ));
        Setup::setSecurity();
        $output->writeln('==================================================');
    }
}
