<?php

declare(strict_types=1);

namespace SymPress\WpCliConsole\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PluginEntryTest extends TestCase
{
    public function testEntryReturnsQuietlyWithoutBundleClassOrVendorAutoloader(): void
    {
        $directory = sys_get_temp_dir() . '/sympress_wp_cli_entry_' . bin2hex(random_bytes(8));
        mkdir($directory);
        copy(dirname(__DIR__, 2) . '/wp-cli-console.php', $directory . '/wp-cli-console.php');
        $code = sprintf('define("ABSPATH", "/"); require %s; echo "loaded";', var_export($directory . '/wp-cli-console.php', true));

        try {
            // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec -- Run the entry file in a clean PHP process without the test autoloader.
            exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($code) . ' 2>&1', $output, $exitCode);
        } finally {
            unlink($directory . '/wp-cli-console.php');
            rmdir($directory);
        }

        self::assertSame(0, $exitCode, implode("\n", $output));
        self::assertSame(['loaded'], $output);
    }
}
