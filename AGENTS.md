# SymPress WP-CLI Console

## Scope and entry points

- `src/Application/WpCliRunner.php` is the only process boundary.
- Commands in `src/Command` stay thin and translate Symfony input to unchanged WP-CLI argument names.
- `tests/Unit/WpCliCommandTest.php` is the command-to-argument contract.

## Verification

- Fast behavior check: `composer tests`.
- Full required check: `composer qa`.
- Runner changes need `WpCliRunnerTest`; command changes need an exact argument assertion.

## Invariants

- Prefer executable `vendor/bin/wp`; otherwise resolve the global `wp` binary.
- Pass commands to `proc_open()` as an argument array and run them from the kernel project directory.
- Add `--no-color` only for undecorated output and never duplicate it.
- Drain stdout and stderr while the process runs; route stderr to the error output when one exists.
- Return the WP-CLI exit code, normalizing an unavailable/invalid process result to `Command::FAILURE`.

## Cross-repository impact and done

- The kernel discovers `WpCliConsoleBundle` and `wp-cli-console/wp-cli-console.php` through `extra.kernel`.
- A change is done when process/argument tests and `composer qa` pass and the README command list matches `AsCommand` attributes.
