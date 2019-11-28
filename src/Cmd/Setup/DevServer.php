<?php

namespace Bedrox\Cmd\Setup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevServer extends Command
{
    /**
     * CLI configuration
     */
    protected function configure()
    {
        $this
            ->setName('bedrox:server:dev')
            ->setAliases(['bd:s:d', 'bedrox:server'])
            ->setDescription('Use PHP Development Server.')
            ->setHelp('Use the framework without Apache, Nginx or others.')
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
            '==================================================',
            'Bedrox PHP Server: Development',
            '=================================================='
        ));
        $output->writeln('Starting PHP ' . phpversion() . ' Development Server...');
        $infosPathRoot = realpath($_SERVER['APP']['ENTITY'] . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public');
        if (!empty($infosPathRoot)) {
            $output->writeln('Listening on http://localhost:8000');
            $output->writeln('Document root is ' . $infosPathRoot);
            $output->writeln('Press Ctrl-C to quit.');
            $output->writeln('--------------------------------------------------');
            $output->writeln('Waiting for a request...');
            $output->writeln('==================================================');
            exec('cd ' . $infosPathRoot . ' && php -S localhost:8000', $exec);
        } else {
            $output->writeln('An error occurs while trying to load the Document root.');
            $output->writeln('Check your configuration for your Entity directory location.');
        }
    }
}
