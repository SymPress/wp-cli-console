<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wp:info|wp:cli:info', description: 'Show WP-CLI runtime information.')]
final class WpInfoCommand extends AbstractWpCliCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->runWpCli(['cli', 'info'], $output);
    }
}
