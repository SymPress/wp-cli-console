<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Tests\Unit;

use SymPress\WpCliConsole\Command\WpCacheFlushCommand;
use SymPress\WpCliConsole\Command\WpOptionGetCommand;
use SymPress\WpCliConsole\Command\WpPluginListCommand;
use SymPress\WpCliConsole\Command\WpRewriteFlushCommand;
use SymPress\WpCliConsole\Tests\Support\RecordingWpCliRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class WpCliCommandTest extends TestCase
{
    public function testPluginListBuildsWpCliArguments(): void
    {
        $runner = new RecordingWpCliRunner();
        $tester = new CommandTester(new WpPluginListCommand($runner));

        $tester->execute([
            '--status' => 'active',
            '--fields' => 'name,status',
            '--format' => 'json',
        ]);

        self::assertSame(
            [['plugin', 'list', '--status=active', '--fields=name,status', '--format=json']],
            $runner->calls,
        );
    }

    public function testOptionGetBuildsWpCliArguments(): void
    {
        $runner = new RecordingWpCliRunner();
        $tester = new CommandTester(new WpOptionGetCommand($runner));

        $tester->execute([
            'option' => 'siteurl',
            '--format' => 'json',
        ]);

        self::assertSame(
            [['option', 'get', 'siteurl', '--format=json']],
            $runner->calls,
        );
    }

    public function testMaintenanceCommandsBuildWpCliArguments(): void
    {
        $cacheRunner = new RecordingWpCliRunner();
        (new CommandTester(new WpCacheFlushCommand($cacheRunner)))->execute([]);

        $rewriteRunner = new RecordingWpCliRunner();
        (new CommandTester(new WpRewriteFlushCommand($rewriteRunner)))->execute(['--hard' => true]);

        self::assertSame([['cache', 'flush']], $cacheRunner->calls);
        self::assertSame([['rewrite', 'flush', '--hard']], $rewriteRunner->calls);
    }
}
