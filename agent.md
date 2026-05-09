# Agent Notes

## Purpose

This repository contains a WordPress plugin that migrates Automatic.css v2/v3 data to ACSS4, primarily for Bricks Builder sites.

## Stack

- PHP 8.0+
- WordPress plugin architecture
- PHPUnit 10 for tests
- No frontend build step

## Important Behavior

- `ACSS_Settings_Migrator` triggers the ACSS4 migration chain and must not stamp a successful DB version on hard failure.
- `ACSS_Elements_Migrator` must traverse nested Bricks element trees, not just top-level nodes.
- `ACSS_Global_Classes_Migrator` should persist both automatic conversions and inline manual-review flags.
- `ACSS_CSS_Transformer` is the core pure-PHP conversion layer and should stay independently testable.

## Working Rules

- Prefer small, focused PHP changes and keep WordPress-specific behavior at the boundaries.
- Add or update PHPUnit coverage for any migration behavior change.
- Preserve manual-review comments instead of forcing unsafe automatic conversions.
- Treat post meta and option writes as destructive operations; avoid changing write behavior without tests.

## Verification

- Main test command: `php vendor/bin/phpunit`
- Check `readme.txt` and `README.md` when changing product positioning or usage instructions.
