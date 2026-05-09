# ACSS3 to ACSS4

ACSS3 to ACSS4 is a WordPress admin utility plugin that helps migrate Automatic.css v2/v3 data to ACSS4 format on Bricks Builder sites.

It focuses on the migration tasks that are awkward to do by hand:

- running the ACSS4 settings migration chain
- converting legacy ACSS CSS tokens inside Bricks element data
- updating ACSS-related Bricks global classes
- flagging CSS that still needs manual review

## Requirements

- WordPress 6.0+
- PHP 8.0+
- ACSS4 installed and active
- Bricks Builder installed and active for Bricks content migration

## What It Does

The plugin adds a screen under `Tools -> ACSS3 to ACSS4` and runs the migration in three steps:

1. ACSS settings
2. Bricks element CSS
3. Bricks global classes

During CSS conversion it rewrites common ACSS3 token patterns to their ACSS4 equivalents and annotates unsupported standalone channel variables for manual cleanup.

## Safety Notes

- Create a full database backup before running the migration.
- The plugin updates WordPress options and post meta in place.
- Some CSS patterns cannot be migrated safely without context and are deliberately flagged instead of rewritten blindly.

## Development

Install dependencies:

```bash
composer install
```

Run tests:

```bash
php vendor/bin/phpunit
```

## Project Structure

- `acss3-to-4.php` plugin bootstrap
- `includes/` migration logic, admin page, and AJAX handlers
- `assets/` admin JavaScript and CSS
- `tests/` PHPUnit coverage for the CSS transformer and migration behavior

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
