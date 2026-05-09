<?php
/**
 * Plugin Name: ACSS3 to ACSS4
 * Plugin URI:  https://bricks2etch.com/
 * Description: Migriert Bricks Websites mit ACSS3 zu ACSS4.
 * Version:     1.0.2
 * Update URI:  https://github.com/tobiashaas/ACSS-three2four/
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author:      Tobias Haas
 * License:     GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACSS3TO4_FILE', __FILE__ );
define( 'ACSS3TO4_VERSION', '1.0.2' );

require_once __DIR__ . '/includes/ACSS_CSS_Transformer.php';
require_once __DIR__ . '/includes/Migrators/ACSS_Settings_Migrator.php';
require_once __DIR__ . '/includes/Migrators/ACSS_Elements_Migrator.php';
require_once __DIR__ . '/includes/Migrators/ACSS_Global_Classes_Migrator.php';
require_once __DIR__ . '/includes/Admin/ACSS_Admin_Page.php';
require_once __DIR__ . '/includes/Admin/ACSS_Ajax_Handler.php';
require_once __DIR__ . '/includes/ACSS_Plugin.php';
require_once __DIR__ . '/includes/ACSS_Updater.php';

$updater = __DIR__ . '/includes/vendor/plugin-update-checker/plugin-update-checker.php';

if ( file_exists( $updater ) ) {
	require_once $updater;
}

add_action(
	'plugins_loaded',
	function () {
		( new ACSS_Updater( ACSS3TO4_FILE ) )->register();

		$transformer = new ACSS_CSS_Transformer();

		$plugin = new ACSS_Plugin(
			new ACSS_Admin_Page(),
			new ACSS_Ajax_Handler(
				new ACSS_Settings_Migrator(),
				new ACSS_Elements_Migrator( $transformer ),
				new ACSS_Global_Classes_Migrator( $transformer )
			)
		);

		$plugin->boot();
	}
);
