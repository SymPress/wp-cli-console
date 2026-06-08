<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wp:user:list', description: 'List WordPress users via WP-CLI.')]
final class WpUserListCommand extends AbstractWpCliCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('role', null, InputOption::VALUE_REQUIRED, 'Filter users by role.')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'Comma-separated fields to display.')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format.', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = ['user', 'list'];
        $this->appendOption($arguments, 'role', $input->getOption('role'));
        $this->appendOption($arguments, 'fields', $input->getOption('fields'));
        $this->appendOption($arguments, 'format', $input->getOption('format'));

        return $this->runWpCli($arguments, $output);
    }
}
