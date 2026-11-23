<?php

/**
 * Plugin Name: WP-CLI Console
 * Description: Symfony Console wrappers for useful WP-CLI workflows.
 * Version: 1.0.5
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
    // A hand-copied MU plugin without vendor/ must not fatal every request.
    if (!is_file(__DIR__ . '/vendor/autoload.php')) {
        return;
    }

    require_once __DIR__ . '/vendor/autoload.php';
}
