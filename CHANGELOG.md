# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)
where applicable.

## [1.0.7] - 2026-11-28

- Update the locked Symfony components to 8.1.12 for the November 25 security advisories.

## [1.0.6] - 2026-11-23

- Pass WP-CLI stdout and stderr through unchanged. Console markup such as
  `<info>` or `\<` in WP-CLI output is no longer interpreted or stripped, so
  `--format=json` stays valid and stored values cannot style the terminal.
  Scripts that relied on the stripped output will see WP-CLI's original bytes.
- Return quietly from the plugin entry file when neither the bundle class nor
  `vendor/autoload.php` is available, instead of a fatal error.
- Bundle `sympress/kernel` 1.1.18 and current Symfony 8.1 patch releases in the
  release archive (the 1.0.5 archive shipped kernel 1.1.13).
- CI: pin `sympress/workflows` to v1.3.2 and let Dependabot update GitHub
  Actions pins.

## [1.0.5] - 2026-11-06

- Use the Symfony 8.1.11 security patches released on November 4.

## [1.0.4] - 2026-10-02

- Fix production-archive Plugin Check findings with documented, call-specific
  exceptions for native shell-free WP-CLI execution and private subprocess pipes.
- Validate forwarded CLI host environment values as DNS names or IP addresses
  with optional valid ports; invalid values fall back to `localhost`.
- Remove broad runner-related coding-standard exclusions. Archive checks remain
  mandatory; published 1.0.3 archives and tags stay unchanged.

## [1.0.3] - 2026-10-02

- Repair archive caller permissions for the pinned reusable build workflow.
- Exclude tests, development documentation, QA configuration and coverage output
  from the production archive while retaining production Composer dependencies,
  artifact checksums and the manifest.
- Align the source plugin header with the new archive release version.
