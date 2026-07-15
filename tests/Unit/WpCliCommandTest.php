<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Tests\Unit;

use SymPress\WpCliConsole\Command\WpCacheFlushCommand;
use SymPress\WpCliConsole\Command\WpCronListCommand;
use SymPress\WpCliConsole\Command\WpDbSizeCommand;
use SymPress\WpCliConsole\Command\WpInfoCommand;
use SymPress\WpCliConsole\Command\WpOptionGetCommand;
use SymPress\WpCliConsole\Command\WpPluginListCommand;
use SymPress\WpCliConsole\Command\WpRewriteFlushCommand;
use SymPress\WpCliConsole\Command\WpThemeListCommand;
use SymPress\WpCliConsole\Command\WpUserListCommand;
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

    public function testRemainingCommandsPreserveWpCliArgumentNames(): void
    {
        $themeRunner = new RecordingWpCliRunner();
        (new CommandTester(new WpThemeListCommand($themeRunner)))->execute([
            '--status' => 'active',
            '--fields' => 'name,status',
            '--format' => 'json',
        ]);

        $userRunner = new RecordingWpCliRunner();
        (new CommandTester(new WpUserListCommand($userRunner)))->execute([
            '--role' => 'editor',
            '--fields' => 'ID,user_login',
            '--format' => 'csv',
        ]);

        $cronRunner = new RecordingWpCliRunner();
        (new CommandTester(new WpCronListCommand($cronRunner)))->execute([
            '--hook' => 'cleanup',
            '--recurrence' => 'hourly',
            '--fields' => 'hook,next_run',
            '--format' => 'json',
        ]);

        $databaseRunner = new RecordingWpCliRunner();
        (new CommandTester(new WpDbSizeCommand($databaseRunner)))->execute([
            '--tables' => true,
            '--human-readable' => true,
            '--format' => 'csv',
        ]);

        $infoRunner = new RecordingWpCliRunner();
        (new CommandTester(new WpInfoCommand($infoRunner)))->execute([]);

        self::assertSame([
            ['theme', 'list', '--status=active', '--fields=name,status', '--format=json'],
        ], $themeRunner->calls);
        self::assertSame([
            ['user', 'list', '--role=editor', '--fields=ID,user_login', '--format=csv'],
        ], $userRunner->calls);
        self::assertSame([
            ['cron', 'event', 'list', '--hook=cleanup', '--recurrence=hourly', '--fields=hook,next_run', '--format=json'],
        ], $cronRunner->calls);
        self::assertSame([
            ['db', 'size', '--tables', '--human-readable', '--format=csv'],
        ], $databaseRunner->calls);
        self::assertSame([['cli', 'info']], $infoRunner->calls);
    }
}
