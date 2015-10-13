<?php

namespace Githooks;

use Githooks\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class App extends Application
{
    /**
     * Override to add local command classes
     *
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands = array_merge($commands, [
            new Command\Install(),
            new Command\Lint(),
        ]);

        return $commands;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function doRunCommand(SymfonyCommand $command, InputInterface $input, OutputInterface $output)
    {
        $output->writeLn('<info>git-hook : ' . $command->getName() . '</info>');

        return parent::doRunCommand($command, $input, $output);
    }
}
