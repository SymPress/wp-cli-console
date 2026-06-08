# Contributing

Thanks for taking the time to improve SymPress WP-CLI Console.

## Local Setup

```bash
composer install
composer test
```

The package uses PHP 8.5, Symfony Console, WP-CLI, and PHPUnit.

## Pull Requests

- Keep pull requests focused on one behavior or documentation change.
- Add or update tests for command behavior changes.
- Run the available checks before opening a pull request.
- Use Conventional Commits for commit messages, for example
  `feat(wp-cli-console): add media library command`.

## Coding Guidelines

- Keep command classes thin and delegate WP-CLI process execution to the runner.
- Preserve WP-CLI argument names when wrapping commands.
- Stream command output without buffering complete process output in memory.
- Prefer explicit command tests that verify the generated WP-CLI arguments.
