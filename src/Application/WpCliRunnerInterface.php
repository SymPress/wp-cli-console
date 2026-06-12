<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Application;

use Symfony\Component\Console\Output\OutputInterface;

interface WpCliRunnerInterface
{
    /** @param list<string> $arguments */
    public function run(array $arguments, OutputInterface $output): int;
}
