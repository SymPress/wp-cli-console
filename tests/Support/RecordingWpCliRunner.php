<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Tests\Support;

use SymPress\WpCliConsole\Application\WpCliRunnerInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RecordingWpCliRunner implements WpCliRunnerInterface
{
    /**
     * @var list<list<string>>
     */
    public array $calls = [];

    public function __construct(
        private readonly int $exitCode = 0,
    ) {
    }

    public function run(array $arguments, OutputInterface $output): int
    {
        $this->calls[] = $arguments;
        $output->writeln('wp-cli called');

        return $this->exitCode;
    }
}
