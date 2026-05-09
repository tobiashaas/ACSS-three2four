# ACSS3 to ACSS4 Quality and Updater Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the migration plugin safer to ship by adding regression coverage, hardening WordPress-facing behavior, enabling in-dashboard updates from GitHub releases, and automating CI/release packaging.

**Architecture:** Keep the current small-plugin structure, expand tests around the existing classes, and add an updater bootstrap layer plus GitHub workflows. Prefer minimal code movement and targeted fixes over refactors so the release risk stays low.

**Tech Stack:** PHP 8.0+, PHPUnit 10, WordPress function shims for integration-style tests, GitHub Actions, plugin-update-checker

---

### Task 1: Spec and Version Constants

**Files:**
- Modify: `acss3-to-4.php`
- Test: `tests/ACSS_Plugin_Test.php`

- [ ] **Step 1: Write the failing test**
- [ ] **Step 2: Run the test to verify it fails**
- [ ] **Step 3: Add shared plugin version/runtime bootstrap changes**
- [ ] **Step 4: Run the focused tests to verify they pass**
- [ ] **Step 5: Commit**

### Task 2: Transformer Regression Coverage

**Files:**
- Modify: `includes/ACSS_CSS_Transformer.php`
- Test: `tests/ACSS_CSS_Transformer_Test.php`

- [ ] **Step 1: Write failing tests for HSL(A) whitespace handling**
- [ ] **Step 2: Run the focused tests to verify failure**
- [ ] **Step 3: Apply the minimal regex fix**
- [ ] **Step 4: Run the focused tests to verify success**
- [ ] **Step 5: Commit**

### Task 3: Admin and AJAX Integration-Style Coverage

**Files:**
- Modify: `tests/bootstrap.php`
- Create: `tests/ACSS_Admin_Page_Test.php`
- Create: `tests/ACSS_Ajax_Handler_Test.php`
- Create: `tests/ACSS_Plugin_Test.php`
- Modify: `includes/Admin/ACSS_Admin_Page.php`
- Modify: `assets/admin.js`

- [ ] **Step 1: Write failing tests for admin hooks, asset localization, AJAX capability/nonce flow, and plugin boot behavior**
- [ ] **Step 2: Run the focused tests to verify failure**
- [ ] **Step 3: Add the smallest production changes and test shims needed**
- [ ] **Step 4: Run the focused tests to verify success**
- [ ] **Step 5: Commit**

### Task 4: Safer Migration Error Handling

**Files:**
- Modify: `includes/Migrators/ACSS_Settings_Migrator.php`
- Modify: `tests/ACSS_Migrators_Test.php`

- [ ] **Step 1: Write failing tests for CSS-regeneration-only failure handling**
- [ ] **Step 2: Run the focused tests to verify failure**
- [ ] **Step 3: Narrow the success-on-failure logic**
- [ ] **Step 4: Run the focused tests to verify success**
- [ ] **Step 5: Commit**

### Task 5: GitHub Updater Integration

**Files:**
- Modify: `composer.json`
- Modify: `composer.lock`
- Modify: `acss3-to-4.php`
- Create or vendor: updater library files

- [ ] **Step 1: Add a failing integration-style test for updater bootstrap if practical**
- [ ] **Step 2: Fetch and wire `plugin-update-checker`**
- [ ] **Step 3: Initialize it against `https://github.com/tobiashaas/ACSS-three2four`**
- [ ] **Step 4: Run focused tests and bootstrap verification**
- [ ] **Step 5: Commit**

### Task 6: CI and Release Automation

**Files:**
- Create: `.github/workflows/ci.yml`
- Create: `.github/workflows/release.yml`
- Create: `.gitattributes` or packaging manifest if needed
- Modify: `README.md`

- [ ] **Step 1: Add workflow files for lint/test and release ZIP packaging**
- [ ] **Step 2: Document the release process**
- [ ] **Step 3: Verify YAML and referenced paths locally as far as possible**
- [ ] **Step 4: Commit**

### Task 7: Final Verification

**Files:**
- Verify current worktree only

- [ ] **Step 1: Run `composer validate --no-check-publish`**
- [ ] **Step 2: Run `php -l` on project PHP files**
- [ ] **Step 3: Run `vendor/bin/phpunit`**
- [ ] **Step 4: Review diff for accidental release noise**
- [ ] **Step 5: Report actual status and residual gaps**
