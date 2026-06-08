<?php

/**
 * Plugin Name: WP-CLI Console
 * Description: Symfony Console wrappers for useful WP-CLI workflows.
 * Version: 1.0.0
 * Requires at least: 6.9
 * Requires PHP: 8.5
 * Author: Brian Schaeffner
 * License: GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SymPress\WpCliConsole;

if (!defined('ABSPATH')) {
    return;
}

if (!class_exists(WpCliConsoleBundle::class)) {
    foreach (
        [
            dirname(__DIR__, 2) . '/vendor/autoload.php',
            dirname(__DIR__) . '/vendor/autoload.php',
            __DIR__ . '/vendor/autoload.php',
        ] as $autoloader
    ) {
        if (!is_readable($autoloader)) {
            continue;
        }

        require_once $autoloader;
        break;
    }
}
