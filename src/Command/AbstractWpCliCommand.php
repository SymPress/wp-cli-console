<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Command;

use SymPress\WpCliConsole\Application\WpCliRunnerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractWpCliCommand extends Command
{
    public function __construct(
        private readonly WpCliRunnerInterface $runner,
    ) {

        parent::__construct();
    }

    /** @param list<string> $arguments */
    protected function runWpCli(array $arguments, OutputInterface $output): int
    {
        return $this->runner->run($arguments, $output);
    }

    protected function positionalValue(mixed $value, string $name): string
    {
        if (!is_string($value) || trim($value) === '' || str_contains($value, "\0") || str_starts_with(ltrim($value), '-')) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Controlled CLI label; exceptions are not HTML output.
            throw new \InvalidArgumentException(sprintf('The %s must be nonempty text without option flags or NUL bytes.', $name));
        }

        return $value;
    }

    /** @param list<string> $arguments */
    protected function addFlag(array &$arguments, string $name, bool $enabled): void
    {
        if (!$enabled) {
            return;
        }

        $arguments[] = sprintf('--%s', $name);
    }

    /** @param list<string> $arguments */
    protected function appendOption(array &$arguments, string $name, mixed $value): void
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return;
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return;
        }

        $arguments[] = sprintf('--%s=%s', $name, $stringValue);
    }
}
