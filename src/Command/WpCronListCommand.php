<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wp:cron:list', description: 'List scheduled WordPress cron events via WP-CLI.')]
final class WpCronListCommand extends AbstractWpCliCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('hook', null, InputOption::VALUE_REQUIRED, 'Filter by hook name.')
            ->addOption('recurrence', null, InputOption::VALUE_REQUIRED, 'Filter by recurrence.')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'Comma-separated fields to display.')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format.', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = ['cron', 'event', 'list'];
        $this->appendOption($arguments, 'hook', $input->getOption('hook'));
        $this->appendOption($arguments, 'recurrence', $input->getOption('recurrence'));
        $this->appendOption($arguments, 'fields', $input->getOption('fields'));
        $this->appendOption($arguments, 'format', $input->getOption('format'));

        return $this->runWpCli($arguments, $output);
    }
}
