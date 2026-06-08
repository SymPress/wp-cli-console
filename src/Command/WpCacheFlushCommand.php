<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wp:cache:flush', description: 'Flush the WordPress object cache via WP-CLI.')]
final class WpCacheFlushCommand extends AbstractWpCliCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->runWpCli(['cache', 'flush'], $output);
    }
}
