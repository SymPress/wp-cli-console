<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wp:rewrite:flush', description: 'Flush WordPress rewrite rules via WP-CLI.')]
final class WpRewriteFlushCommand extends AbstractWpCliCommand
{
    protected function configure(): void
    {
        $this->addOption('hard', null, InputOption::VALUE_NONE, 'Update .htaccess rules as well.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = ['rewrite', 'flush'];
        $this->addFlag($arguments, 'hard', (bool) $input->getOption('hard'));

        return $this->runWpCli($arguments, $output);
    }
}
