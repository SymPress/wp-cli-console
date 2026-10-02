# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)
where applicable.

## [1.0.3] - 2026-10-02

- Repair archive caller permissions for the pinned reusable build workflow.
- Exclude tests, development documentation, QA configuration and coverage output
  from the production archive while retaining production Composer dependencies,
  artifact checksums and the manifest.
- Align the source plugin header with the new archive release version.
