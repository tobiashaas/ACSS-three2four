=== ACSS3 to ACSS4 ===
Contributors: tobiashaas
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 6.8
Stable tag: 1.0.6
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Migriert Bricks Websites mit ACSS3 zu ACSS4.

== Description ==

ACSS3 to ACSS4 adds a guided migration screen under Tools and helps convert the most common Automatic.css v2/v3 references used inside Bricks content and global classes.

The migration runs in three steps:

1. Trigger the ACSS4 settings migration chain
2. Convert Bricks element CSS stored in post meta
3. Update Bricks global classes and ACSS size tokens

Before running the migration:

- Install and activate ACSS4
- Install and activate Bricks Builder if you want to migrate Bricks content
- Create a full database backup

The plugin also flags CSS patterns that still need manual review after the automated pass.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate **ACSS3 to ACSS4**
3. Open **Tools -> ACSS3 to ACSS4**
4. Create a backup
5. Start the migration

== Frequently Asked Questions ==

= Does this replace ACSS4? =

No. ACSS4 must already be installed. This plugin only helps migrate legacy data into the newer format.

= What gets migrated automatically? =

The plugin migrates ACSS settings, Bricks element CSS stored in the supported Bricks meta keys, and Bricks global classes that belong to ACSS.

= What still needs manual review? =

Standalone color channel variables such as `--primary-h`, `--primary-r`, or similar token fragments are flagged inline because they cannot always be converted safely without context.

= Is a backup required? =

Yes. The migration updates WordPress options and post meta in place.

== Changelog ==

= 1.0.6 =

* Fix Step 2 getting stuck in repeated AJAX calls when no Bricks posts are found or progress stalls.

= 1.0.5 =

* Fix release ZIP packaging so WordPress updates install into the stable `acss3-to-4` plugin folder.

= 1.0.4 =

* Allow Step 1 to use detected ACSS 4.x database versions when the ACSS plugin version constant is unavailable.

= 1.0.3 =

* Fix the migration screen scripts not loading on the ACSS submenu page.

= 1.0.2 =

* Allow ACSS 4 prerelease versions like RC, beta, and dev builds.
* Show the migration screen as an ACSS submenu with a Tools fallback.
* Update the plugin description and plugin website link.

= 1.0.1 =

* Add GitHub-based in-dashboard updates and automated release packaging.
* Expand regression coverage for transformer, admin, AJAX, updater, and migration flows.
* Harden migration error handling and fix HSL(A) conversion edge cases.

= 1.0.0 =

* Initial release
