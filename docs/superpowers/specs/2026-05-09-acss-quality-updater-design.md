# ACSS3 to ACSS4 Quality and Updater Design

## Goal

Raise the plugin from "good local utility" to a safer, releasable migration plugin by tightening regression coverage, improving WordPress-facing robustness, adding in-dashboard updates from public GitHub releases, and automating CI/release packaging.

## Scope

- Fix the CSS transformer regression around HSL(A) parsing variants.
- Expand automated coverage beyond pure transformer/migrator units into WordPress-facing integration-style behavior.
- Improve runtime safety in AJAX and admin flows where failures currently surface too generically.
- Add a GitHub release-backed updater so WordPress sites can discover and install new versions.
- Add GitHub Actions to test and package release ZIPs consistently.

## Non-Goals

- No rewrite to namespaces or a new plugin architecture.
- No external update server.
- No browser E2E tests in this pass.

## Architecture

### Test Layers

Keep the fast existing PHPUnit unit tests for the transformer and migrators. Add a second layer of integration-style PHPUnit tests that exercise the plugin's WordPress touchpoints through controlled function shims:

- `ACSS_Admin_Page`
- `ACSS_Ajax_Handler`
- `ACSS_Plugin`

This does not replace future `WP_UnitTestCase` coverage, but it closes the highest-risk gaps immediately without requiring a full WordPress bootstrap in the repository.

### Runtime Hardening

- Fix the broken HSL(A) regex so valid ACSS3 syntax with trailing whitespace is converted.
- Return clearer AJAX failures for invalid JSON/HTTP responses in the admin UI.
- Avoid treating every error containing `CSS` as a successful migration; narrow success-on-failure behavior to the intended CSS-regeneration case.
- Centralize plugin version usage to avoid version drift between assets and headers.

### Updater

Use `plugin-update-checker` against the public GitHub repository releases. Initialize it only in WordPress runtime and point it at the GitHub repository so WordPress can surface updates when a new release is published.

### Release Automation

Add two GitHub workflows:

- CI workflow for PHP linting and PHPUnit
- Release workflow that builds a distributable ZIP and attaches it to GitHub releases

The updater will then consume those releases.

## Verification Strategy

- PHPUnit regression tests for transformer edge cases
- PHPUnit integration-style tests for admin page registration, AJAX guards, and boot behavior
- `php -l` on project PHP files
- `composer validate --no-check-publish`
- `vendor/bin/phpunit`

## Risks

- GitHub API/rate-limit behavior is delegated to `plugin-update-checker`.
- Integration-style tests still stop short of a real WordPress core bootstrap; they are an immediate risk reduction, not the final ceiling.
