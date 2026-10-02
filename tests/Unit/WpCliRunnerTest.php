<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;
use SymPress\Kernel\Kernel\KernelInterface;
use SymPress\WpCliConsole\Application\WpCliRunner;

final class WpCliRunnerTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $server;

    private ?string $projectDirectory = null;
    private ?string $globalBinDirectory = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->server = $_SERVER;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $_SERVER = $this->server;

        if ($this->projectDirectory === null || !is_dir($this->projectDirectory)) {
            return;
        }

        if (is_file($this->projectDirectory . '/vendor/bin/wp')) {
            unlink($this->projectDirectory . '/vendor/bin/wp');
            rmdir($this->projectDirectory . '/vendor/bin');
            rmdir($this->projectDirectory . '/vendor');
        }

        rmdir($this->projectDirectory);

        if ($this->globalBinDirectory !== null && is_dir($this->globalBinDirectory)) {
            unlink($this->globalBinDirectory . '/wp');
            rmdir($this->globalBinDirectory);
        }
    }

    public function testItRunsTheLocalBinaryWithEnvironmentAndStreamsBothOutputs(): void
    {
        $projectDirectory = $this->projectWithLocalWpBinary();
        $_SERVER['HTTP_HOST'] = 'shop.example.test';
        $_SERVER['SERVER_NAME'] = 'wordpress.example.test';
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($projectDirectory);
        $output = new BufferedOutput();

        $exitCode = (new WpCliRunner($kernel))->run(['plugin', 'list'], $output);
        $stdout = $output->fetch();

        self::assertSame(7, $exitCode);
        self::assertStringContainsString($projectDirectory, $stdout);
        self::assertStringContainsString('shop.example.test|wordpress.example.test', $stdout);
        self::assertStringContainsString('["plugin","list","--no-color"]', $stdout);
        self::assertStringContainsString("wp-cli stderr\n", $stdout);
    }

    public function testItFallsBackToTheGlobalWpBinary(): void
    {
        $this->projectDirectory = sys_get_temp_dir() . '/sympress_wp_cli_project_' . bin2hex(random_bytes(8));
        $this->globalBinDirectory = sys_get_temp_dir() . '/sympress_wp_cli_bin_' . bin2hex(random_bytes(8));
        mkdir($this->projectDirectory);
        mkdir($this->globalBinDirectory);
        $binary = $this->globalBinDirectory . '/wp';
        file_put_contents($binary, <<<'PHP'
#!/usr/bin/env php
<?php

fwrite(STDOUT, 'global:' . json_encode(array_slice($argv, 1), JSON_THROW_ON_ERROR));
PHP);
        chmod($binary, 0755);
        $previousPath = getenv('PATH');
        putenv('PATH=' . $this->globalBinDirectory . PATH_SEPARATOR . ($previousPath === false ? '' : $previousPath));
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->projectDirectory);
        $output = new BufferedOutput();

        try {
            $exitCode = (new WpCliRunner($kernel))->run(['cli', 'info'], $output);
        } finally {
            $previousPath === false ? putenv('PATH') : putenv('PATH=' . $previousPath);
        }

        self::assertSame(0, $exitCode);
        self::assertSame('global:["cli","info","--no-color"]', $output->fetch());
    }

    public function testItPreservesValidatedHostAndIpEnvironmentValues(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->projectWithLocalWpBinary());
        $runner = new WpCliRunner($kernel);
        foreach (['localhost', 'shop.example.test:8443', '127.0.0.1:8080', '[2001:db8::1]:443', '::1', 'xn--bcher-kva.example'] as $host) {
            $_SERVER['HTTP_HOST'] = $host;
            $_SERVER['SERVER_NAME'] = $host;
            $output = new BufferedOutput();
            self::assertSame(7, $runner->run(['cli', 'info'], $output));
            self::assertStringContainsString($host . '|' . $host, $output->fetch());
        }
    }

    public function testItRejectsUnsafeHostEnvironmentValues(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->projectWithLocalWpBinary());
        $runner = new WpCliRunner($kernel);
        foreach (['user@example.test', 'example.test/path', 'example.test\\path', "example.test\r\nINJECTED=value", "example.test\0", ' example.test', '-bad.example', '[not-an-ip]', 'example.test:0', 'example.test:65536', str_repeat('a', 64) . '.example', str_repeat('a', 260), []] as $host) {
            $_SERVER['HTTP_HOST'] = $host;
            $_SERVER['SERVER_NAME'] = $host;
            $output = new BufferedOutput();
            self::assertSame(7, $runner->run(['cli', 'info'], $output));
            self::assertStringContainsString("localhost|localhost\n", $output->fetch());
        }
    }

    public function testItValidatesProcessEnvironmentFallbackValues(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->projectWithLocalWpBinary());
        unset($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']);
        $previousHost = getenv('HTTP_HOST');
        $previousName = getenv('SERVER_NAME');
        putenv('HTTP_HOST=shop.example.test:8443');
        putenv('SERVER_NAME=https://unsafe.example');
        $output = new BufferedOutput();
        try {
            self::assertSame(7, (new WpCliRunner($kernel))->run(['cli', 'info'], $output));
            self::assertStringContainsString("shop.example.test:8443|localhost\n", $output->fetch());
        } finally {
            $previousHost === false ? putenv('HTTP_HOST') : putenv('HTTP_HOST=' . $previousHost);
            $previousName === false ? putenv('SERVER_NAME') : putenv('SERVER_NAME=' . $previousName);
        }
    }

    public function testItPassesShellMetacharactersLiterallyWithoutDuplicatingNoColor(): void
    {
        $projectDirectory = $this->projectWithLocalWpBinary();
        $marker = $projectDirectory . '/shell-must-not-run';
        $arguments = ['option', 'get', 'value with spaces; $(touch ' . $marker . ')', '--no-color'];
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($projectDirectory);
        $output = new BufferedOutput();
        self::assertSame(7, (new WpCliRunner($kernel))->run($arguments, $output));
        self::assertStringContainsString(json_encode($arguments, JSON_THROW_ON_ERROR), $output->fetch());
        self::assertFileDoesNotExist($marker);
    }

    public function testItReturnsFailureWhenNoWpBinaryCanBeStarted(): void
    {
        $this->projectDirectory = sys_get_temp_dir() . '/sympress_wp_cli_project_' . bin2hex(random_bytes(8));
        mkdir($this->projectDirectory);
        $previousPath = getenv('PATH');
        putenv('PATH=/path/without/wp');
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->projectDirectory);
        $output = new BufferedOutput();

        try {
            $exitCode = (new WpCliRunner($kernel))->run(['cli', 'info'], $output);
        } finally {
            $previousPath === false ? putenv('PATH') : putenv('PATH=' . $previousPath);
        }

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertSame('', $output->fetch());
    }

    private function projectWithLocalWpBinary(): string
    {
        $this->projectDirectory = sys_get_temp_dir() . '/sympress_wp_cli_' . bin2hex(random_bytes(8));
        mkdir($this->projectDirectory . '/vendor/bin', 0777, true);
        $binary = $this->projectDirectory . '/vendor/bin/wp';
        file_put_contents($binary, <<<'PHP'
#!/usr/bin/env php
<?php

fwrite(STDOUT, getcwd() . "\n");
fwrite(STDOUT, getenv('HTTP_HOST') . '|' . getenv('SERVER_NAME') . "\n");
fwrite(STDOUT, json_encode(array_slice($argv, 1), JSON_THROW_ON_ERROR) . "\n");
fwrite(STDERR, "wp-cli stderr\n");

exit(7);
PHP);
        chmod($binary, 0755);

        return $this->projectDirectory;
    }
}
