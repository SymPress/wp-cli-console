<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wp:plugin:list', description: 'List installed WordPress plugins via WP-CLI.')]
final class WpPluginListCommand extends AbstractWpCliCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Filter by status: active, inactive, must-use, dropin.')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'Comma-separated fields to display.')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format.', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = ['plugin', 'list'];
        $this->appendOption($arguments, 'status', $input->getOption('status'));
        $this->appendOption($arguments, 'fields', $input->getOption('fields'));
        $this->appendOption($arguments, 'format', $input->getOption('format'));

        return $this->runWpCli($arguments, $output);
    }
}
