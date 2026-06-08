<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wp:db:size', description: 'Show WordPress database size via WP-CLI.')]
final class WpDbSizeCommand extends AbstractWpCliCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('tables', null, InputOption::VALUE_NONE, 'Show each table individually.')
            ->addOption('human-readable', null, InputOption::VALUE_NONE, 'Display sizes in a human-readable format.')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format.', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = ['db', 'size'];
        $this->addFlag($arguments, 'tables', (bool) $input->getOption('tables'));
        $this->addFlag($arguments, 'human-readable', (bool) $input->getOption('human-readable'));
        $this->appendOption($arguments, 'format', $input->getOption('format'));

        return $this->runWpCli($arguments, $output);
    }
}
