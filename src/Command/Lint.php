<?php

namespace Githooks\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Lint extends Command
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function configure()
    {
        $this
          ->setName('php:lint')
          ->setDescription('Lint committed (cached) php files')
          ;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process('git diff --cached --name-only');
        $process->run();

        $result = trim($process->getOutput());
        if (empty($result)) {
            $output->writeLn('<info>nothing to do</info>');
            exit(0);
        }
        $result = explode("\n", $result);
        $errors = false;
        foreach ($result as $path) {
            if ('.php' != substr($path, -4)) {
                $output->writeLn('[-] ' . $path);
                continue;
            }
            $command = sprintf('%s -l %s', PHP_BINARY, $path);
            $process = new Process($command);
            $process->run();
            if ($process->isSuccessful()) {
                $output->writeLn('<info>[✓]</info> ' . $path);
                continue;
            }
            $errors = true;
            $output->writeLn('<error>[x]</error> ' . $path);
        }
        if (true == $errors) {
            $output->writeln('<error>lint errors found</error>');
            exit(1);
        } else {
            $output->writeln('<info>no lint errors</info>');
        }
    }
}
