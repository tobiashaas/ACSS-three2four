# ACSS Recursive Bricks Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate recognized ACSS3 token patterns across recursive Bricks data structures and expose detailed logs for changed posts and classes.

**Architecture:** Reuse the existing pure-PHP transformer for pattern detection and rewriting, but move migrators from narrow field-specific writes to recursive string scanning over nested Bricks arrays. Return bounded per-entity change summaries so the admin UI can report what changed without guessing.

**Tech Stack:** PHP 8+, WordPress plugin APIs, PHPUnit 10, vanilla admin JavaScript

---

### Task 1: Add failing migrator tests

**Files:**
- Modify: `tests/ACSS_Migrators_Test.php`

- [ ] **Step 1: Write the failing test**

Add tests for:
- recursive string migration in post meta outside `settings._css`
- no-op behavior for non-ACSS strings
- recursive string migration in `bricks_global_classes`
- detailed return payloads for changed posts/classes

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit tests/ACSS_Migrators_Test.php`
Expected: FAIL because current migrators only scan `_css` and `settings.css`, and do not return detailed change data.

- [ ] **Step 3: Write minimal implementation**

Implement only enough recursive traversal and payload shape to satisfy the new tests.

- [ ] **Step 4: Run test to verify it passes**

Run: `php vendor/bin/phpunit tests/ACSS_Migrators_Test.php`
Expected: PASS

### Task 2: Implement recursive traversal in migrators

**Files:**
- Modify: `includes/Migrators/ACSS_Elements_Migrator.php`
- Modify: `includes/Migrators/ACSS_Global_Classes_Migrator.php`

- [ ] **Step 1: Add shared recursive walk behavior**

Create focused private helpers in each migrator to walk nested arrays, transform matching strings, and collect counts/samples without changing unrelated values.

- [ ] **Step 2: Preserve existing entity write boundaries**

Only call `update_post_meta` / `update_option` when a traversed structure actually changed.

- [ ] **Step 3: Return structured log data**

Include aggregate counts plus bounded details per changed post/class for the admin UI.

- [ ] **Step 4: Run targeted tests**

Run: `php vendor/bin/phpunit tests/ACSS_Migrators_Test.php`
Expected: PASS

### Task 3: Expose detailed admin logging

**Files:**
- Modify: `assets/admin.js`

- [ ] **Step 1: Update step 2 logging**

Render detailed post-level log lines from the new AJAX payload after the aggregate summary.

- [ ] **Step 2: Update step 3 logging**

Render detailed class-level log lines including class-name rename and string-value migration summaries.

- [ ] **Step 3: Keep output bounded and readable**

Show compact summaries and a small number of samples per entity.

- [ ] **Step 4: Re-run migrator tests if payload shape changed**

Run: `php vendor/bin/phpunit tests/ACSS_Migrators_Test.php`
Expected: PASS

### Task 4: Full verification

**Files:**
- Verify only

- [ ] **Step 1: Run full test suite**

Run: `php vendor/bin/phpunit`
Expected: PASS with 0 failures

- [ ] **Step 2: Review diff for accidental scope creep**

Confirm changes are limited to migrators, tests, docs, and admin logging.
