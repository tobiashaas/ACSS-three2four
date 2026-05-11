# Recursive Bricks Migration Design

**Goal:** Expand ACSS3-to-ACSS4 migration so Bricks data is scanned recursively and any string containing recognized ACSS3 token patterns is migrated, while surfacing a more detailed migration log.

**Scope**

- Traverse Bricks post meta structures recursively instead of only `settings._css`.
- Traverse ACSS-related `bricks_global_classes` structures recursively instead of only `settings.css`.
- Continue to migrate only when the existing transformer detects a known ACSS3 pattern.
- Preserve manual-review annotations for unsupported channel-variable cases.
- Improve reporting so migrations can show which posts/classes were changed and examples of what was rewritten.

**Non-Goals**

- Do not rewrite arbitrary strings without a recognized ACSS pattern.
- Do not introduce Bricks-schema-specific field whitelists unless required for compatibility.
- Do not alter the ACSS settings migration chain behavior.

**Architecture**

- Add a recursive walker in the migration layer that visits nested arrays and transforms string values in place.
- Keep all token rewrite rules in `ACSS_CSS_Transformer`; the walker only decides where to apply it and records changes.
- Extend migrator return payloads with per-entity summaries and bounded change samples for the admin log.

**Data Flow**

1. Step 2 loads Bricks post meta arrays.
2. The recursive walker scans every nested string.
3. When the transformer reports conversions or flags, the string is replaced and the change is recorded against the post/meta key.
4. Step 3 repeats the same process for ACSS global classes and still renames class names where needed.
5. Admin JS renders aggregate counts plus detailed lines for changed posts/classes.

**Logging**

- Keep current top-level success lines.
- Add structured details such as post ID, meta key, class name, conversion count, flag count, and a small sample of before/after changes.
- Bound the number of returned samples per entity to keep AJAX payloads reasonable.

**Testing**

- Add PHPUnit coverage proving recursive migration of nested non-`_css` string values.
- Add PHPUnit coverage proving unrelated strings remain untouched.
- Add PHPUnit coverage proving recursive migration inside `bricks_global_classes` beyond `settings.css`.
- Add PHPUnit coverage for detailed reporting payloads so admin logging can rely on stable keys.
