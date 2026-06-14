# SymPress WP-CLI Console

[![Checks](https://img.shields.io/github/actions/workflow/status/SymPress/wp-cli-console/qa.yml?branch=main&label=checks)](https://github.com/SymPress/wp-cli-console/actions/workflows/qa.yml) [![Release](https://img.shields.io/github/v/release/SymPress/wp-cli-console?label=release)](https://github.com/SymPress/wp-cli-console/releases) [![PHP](https://img.shields.io/packagist/dependency-v/sympress/wp-cli-console/php.svg?label=php)](https://packagist.org/packages/sympress/wp-cli-console) [![Downloads](https://img.shields.io/packagist/dt/sympress/wp-cli-console.svg?label=downloads)](https://packagist.org/packages/sympress/wp-cli-console/stats) [![License: GPL-2.0-or-later](https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg)](LICENSE) [![Security Policy](https://img.shields.io/badge/security-policy-2ea44f.svg)](SECURITY.md)

Symfony Console wrappers for useful WP-CLI workflows in SymPress WordPress
kernel applications.

The package exposes common WP-CLI operations as Symfony Console commands. It is
distributed as a Composer-powered WordPress MU plugin and integrates with the
SymPress kernel service container.

## Installation

```bash
composer require sympress/wp-cli-console
```

The package requires PHP 8.5, WordPress 6.9 or newer, `sympress/kernel`, and
`symfony/console`.

## Features

- Symfony Console commands backed by WP-CLI
- Automatic use of the local `vendor/bin/wp` binary when available
- Object cache and rewrite rule maintenance commands
- Plugin, theme, user, cron, option, and database inspection commands
- Streaming stdout and stderr handling for long-running WP-CLI processes
- Kernel service registration through `SymPress\WpCliConsole\WpCliConsoleBundle`

## Commands

```text
wp:cache:flush       Flush the WordPress object cache
wp:rewrite:flush     Flush WordPress rewrite rules
wp:info              Show WP-CLI runtime information
wp:plugin:list       List installed plugins
wp:theme:list        List installed themes
wp:user:list         List WordPress users
wp:cron:list         List scheduled cron events
wp:option:get        Read a WordPress option
wp:db:size           Show WordPress database size
```

## Usage

When the SymPress kernel discovers the package, it registers
`SymPress\WpCliConsole\WpCliConsoleBundle` and loads
`wp-cli-console/wp-cli-console.php` as the MU plugin entry point.

Commands are autoconfigured from `src/Command` and can be run through the
project's Symfony Console entry point:

```bash
bin/console wp:plugin:list --status=active --format=table
bin/console wp:option:get siteurl --format=json
bin/console wp:rewrite:flush --hard
```

The runner executes WP-CLI from the kernel project directory and falls back to
the global `wp` binary when `vendor/bin/wp` is not executable.

## Development

```bash
composer install
composer test
```

## License

This package is licensed under `GPL-2.0-or-later`.
