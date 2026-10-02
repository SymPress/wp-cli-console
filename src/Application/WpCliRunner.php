<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Application;

use SymPress\Kernel\Kernel\KernelInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ExecutableFinder;

final readonly class WpCliRunner implements WpCliRunnerInterface
{
    public function __construct(
        private KernelInterface $kernel,
    ) {
    }

    /** @param list<string> $arguments */
    public function run(array $arguments, OutputInterface $output): int
    {
        $command = $this->command($arguments, $output);

        if ($command === null) {
            return Command::FAILURE;
        }

        $pipes = [];

        // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found, WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open -- Native WP-CLI argv bypasses the shell at this single reviewed CLI process boundary.
        $process = proc_open(
            $command,
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

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Close private subprocess stdin, not a WordPress file.
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $this->drainPipe($pipes[1], 'out', $output);
            $this->drainPipe($pipes[2], 'err', $output);
            usleep(10000);
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Close private subprocess stdout, not a WordPress file.
        fclose($pipes[1]);
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Close private subprocess stderr, not a WordPress file.
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return $exitCode >= 0 ? $exitCode : Command::FAILURE;
    }

    /**
     * @param list<string> $arguments
     * @return list<string>|null
     */
    private function command(array $arguments, OutputInterface $output): ?array
    {
        $binary = $this->wpBinary();

        if ($binary === null) {
            return null;
        }

        $command = [$binary, ...$arguments];

        if (!$output->isDecorated() && !in_array('--no-color', $command, true)) {
            $command[] = '--no-color';
        }

        return $command;
    }

    private function wpBinary(): ?string
    {
        $binary = sprintf('%s/vendor/bin/wp', $this->kernel->getProjectDir());

        if (is_file($binary) && is_executable($binary)) {
            return $binary;
        }

        return (new ExecutableFinder())->find('wp');
    }

    /** @return array<string, string> */
    private function environment(): array
    {
        $environment = getenv();

        $environment['HTTP_HOST'] = $this->serverEnvironmentValue('HTTP_HOST', $environment);
        $environment['SERVER_NAME'] = $this->serverEnvironmentValue('SERVER_NAME', $environment);

        return $environment;
    }

    /** @param array<string, string> $environment */
    private function serverEnvironmentValue(string $name, array $environment): string
    {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- CLI host tokens are not WordPress-slashed; strict DNS/IP/port validation below rejects unsafe values.
        $value = $_SERVER[$name] ?? $environment[$name] ?? 'localhost';

        if (!is_string($value) || strlen($value) > 259) {
            return 'localhost';
        }
        if (filter_var($value, FILTER_VALIDATE_IP) !== false) {
            return $value;
        }
        if (
            preg_match('/^(\[[0-9A-Fa-f:.]+\]|[A-Za-z0-9.-]+)(?::([0-9]{1,5}))?$/D', $value, $parts) !== 1
            || (isset($parts[2]) && ((int) $parts[2] < 1 || (int) $parts[2] > 65535))
        ) {
            return 'localhost';
        }
        $host = $parts[1];
        if (str_starts_with($host, '[')) {
            return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false ? $value : 'localhost';
        }

        return strlen($host) <= 253 && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false ? $value : 'localhost';
    }

    /** @param resource $pipe */
    private function drainPipe(mixed $pipe, string $type, OutputInterface $output): void
    {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread -- Drain the private subprocess pipe incrementally; WP_Filesystem cannot read process streams.
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
