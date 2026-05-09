<?php
/**
 * Plugin Name: ACSS3 to ACSS4
 * Description: Migrates Automatic.css v2/v3 data to v4 format in Bricks Builder sites.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author:      Tobias Haas
 * License:     GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACSS3TO4_FILE', __FILE__ );

require_once __DIR__ . '/includes/ACSS_CSS_Transformer.php';
require_once __DIR__ . '/includes/Migrators/ACSS_Settings_Migrator.php';
require_once __DIR__ . '/includes/Migrators/ACSS_Elements_Migrator.php';
require_once __DIR__ . '/includes/Migrators/ACSS_Global_Classes_Migrator.php';
require_once __DIR__ . '/includes/Admin/ACSS_Admin_Page.php';
require_once __DIR__ . '/includes/Admin/ACSS_Ajax_Handler.php';
require_once __DIR__ . '/includes/ACSS_Plugin.php';

add_action(
	'plugins_loaded',
	function () {
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
