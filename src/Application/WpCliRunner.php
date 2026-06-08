<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Application;

use SymPress\Kernel\Kernel\KernelInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class WpCliRunner implements WpCliRunnerInterface
{
    public function __construct(
        private KernelInterface $kernel,
    ) {
    }

    /**
     * @param list<string> $arguments
     */
    public function run(array $arguments, OutputInterface $output): int
    {
        $process = proc_open(
            $this->command($arguments, $output),
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $this->kernel->getProjectDir(),
            $this->environment(),
        );

        if (!is_resource($process)) {
            return Command::FAILURE;
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $this->drainPipe($pipes[1], 'out', $output);
            $this->drainPipe($pipes[2], 'err', $output);
            usleep(10000);
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return $exitCode >= 0 ? $exitCode : Command::FAILURE;
    }

    /**
     * @param list<string> $arguments
     * @return list<string>
     */
    private function command(array $arguments, OutputInterface $output): array
    {
        $command = [$this->wpBinary(), ...$arguments];

        if (!$output->isDecorated() && !in_array('--no-color', $command, true)) {
            $command[] = '--no-color';
        }

        return $command;
    }

    private function wpBinary(): string
    {
        $binary = sprintf('%s/vendor/bin/wp', $this->kernel->getProjectDir());

        if (is_file($binary) && is_executable($binary)) {
            return $binary;
        }

        return 'wp';
    }

    /**
     * @return array<string, string>
     */
    private function environment(): array
    {
        $environment = getenv();

        if (!is_array($environment)) {
            $environment = [];
        }

        $environment['HTTP_HOST'] = (string) ($_SERVER['HTTP_HOST'] ?? $environment['HTTP_HOST'] ?? 'localhost');
        $environment['SERVER_NAME'] = (string) ($_SERVER['SERVER_NAME'] ?? $environment['SERVER_NAME'] ?? 'localhost');

        return $environment;
    }

    /**
     * @param resource $pipe
     */
    private function drainPipe(mixed $pipe, string $type, OutputInterface $output): void
    {
        while (($buffer = fread($pipe, 8192)) !== false && $buffer !== '') {
            $this->write($type, $buffer, $output);
        }
    }

    private function write(string $type, string $buffer, OutputInterface $output): void
    {
        if ($type !== 'err') {
            $output->write($buffer);

            return;
        }

        if ($output instanceof ConsoleOutputInterface) {
            $output->getErrorOutput()->write($buffer);

            return;
        }

        $output->write($buffer);
    }
}
