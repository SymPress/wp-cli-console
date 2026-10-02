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
composer qa
```

## License

This package is licensed under `GPL-2.0-or-later`.

## Release archives

The archive workflow and archive check use the reviewed reusable workflow commit
`177fa0d727b278d2103052ec77c102b4a4c492a0`. Releases install production Composer
dependencies and include the artifact manifest and checksums. Tests, development
documentation, QA configuration and coverage output are excluded. Source ZIPs
also use `.gitattributes` export exclusions. Artifact attestation remains disabled.

Version 1.0.4 resolves the native-runner Plugin Check findings in a fresh archive; earlier
tags and published archives retain their original contents. The new archive must
pass the hosted archive check before it is attached to the release.

The only native process boundary passes a resolved WP-CLI executable and argument
array to `proc_open()`, with no shell. Its call-specific PHPCS exceptions also
cover private subprocess pipes; `WP_Filesystem` cannot manage those streams.
`HTTP_HOST` and `SERVER_NAME` retain valid DNS/IP values and optional TCP ports.
Invalid values, control characters and URLs fall back to `localhost`; WordPress
text sanitization or unslashing would change these CLI environment tokens.
These exceptions travel with the production source and do not disable archive
checks or unrelated process, filesystem and input-validation checks.

## Positional value validation

Externally supplied positional option names are validated before invoking the
runner: empty/whitespace-only names, NUL, and leading option flags (including
--exec, --require, --ssh, --path, --url and short flags) are rejected. Legitimate
underscore/hyphen option names remain accepted. Runner-generated global/options
arguments continue to be passed as argv entries; shell escaping alone cannot
prevent WP-CLI global flag injection through an untrusted positional value.
