<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wp:option:get', description: 'Read a WordPress option via WP-CLI.')]
final class WpOptionGetCommand extends AbstractWpCliCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('option', InputArgument::REQUIRED, 'Option name.')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format.', 'var_export');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $option = (string) $input->getArgument('option');
        $arguments = ['option', 'get', $option];
        $this->appendOption($arguments, 'format', $input->getOption('format'));

        return $this->runWpCli($arguments, $output);
    }
}
