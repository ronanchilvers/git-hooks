<?php

namespace Githooks\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Install extends Command
{
    /**
     * An array of commands that we can schedule
     *
     * @var array
     */
    protected $options = [
        'php:lint'
    ];

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function configure()
    {
        $this
          ->setName('install')
          ->setDescription('Install git-hooks in the current repository')
          ->addArgument(
            'hook',
            InputArgument::REQUIRED,
            'The hook to create'
          )
          ;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cwd = getcwd();
        if (!is_dir('.git')) {
            $output->writeLn('<error>' . $cwd . ' doesn\'t seem to be a git repository</error>');
            exit(1);
        }
        $hook     = $input->getArgument('hook');
        $filename = ".git/hooks/{$hook}";
        $helper   = $this->getHelper('question');
        $fs       = new Filesystem();
        if ($fs->exists($filename)) {
            $question = new ConfirmationQuestion(
                "A {$hook} hook exists already - do you want to overwrite it? (y/n) ",
                false
            );
            if (!$helper->ask($input, $output, $question)) {
                $output->writeLn("<info>Not overwriting {$hook} hook - bye!</info>");
                exit(0);
            }
        }

        $paths  = [];
        $output->writeLn("<info>Select commands to run on {$hook} (y/n):</info>");
        foreach ($this->options as $option) {
            $question = new ConfirmationQuestion(
                $option . ' ? ',
                false
            );
            if (!$helper->ask($input, $output, $question)) {
                $output->writeLn("<comment>[-]</comment> {$option}");
                continue;
            }
            $output->writeLn("<info>[+]</info> {$option}");
            $paths[$option] = APP_COMMAND . ' ' . $option;
        }

        $script = [
            '#!/bin/bash',
            "# git-hooks - compiled {$hook} hook script - do not edit",
            '# compiled : ' . date('Y-m-d H:i:s'),
            "\n"
        ];

        foreach ($paths as $option => $path) {
            $script[] = "# Option : $option";
            $script[] = "/usr/bin/env php $path";
            $script[] = "if [ \$? -ne 0 ]; then";
            $script[] = "\texit 1";
            $script[] = "fi";
            $script[] = "\n";
        }
        $script[] = "exit 0";
        $script[] = "\n";

        try {
            $fs->dumpFile($filename, implode("\n", $script));
            $fs->chmod($filename, 0755);
            $output->writeLn("<info>[✓]</info> Wrote {$hook} hook");
        } catch (Exception $ex) {
            $output->writeLn($ex->getMessage());
            exit(1);
        }
    }
}
