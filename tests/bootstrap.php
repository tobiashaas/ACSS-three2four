<?php

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ );
}

if ( ! defined( 'ACSS3TO4_FILE' ) ) {
	define( 'ACSS3TO4_FILE', dirname( __DIR__ ) . '/acss3-to-4.php' );
}

if ( ! defined( 'ACSS3TO4_VERSION' ) ) {
	define( 'ACSS3TO4_VERSION', '1.0.4' );
}

$GLOBALS['acss_test_options']    = [];
$GLOBALS['acss_test_transients'] = [];
$GLOBALS['acss_test_actions']    = [];
$GLOBALS['acss_test_management_pages'] = [];
$GLOBALS['acss_test_submenu_pages'] = [];
$GLOBALS['acss_test_styles']     = [];
$GLOBALS['acss_test_scripts']    = [];
$GLOBALS['acss_test_localized']  = [];
$GLOBALS['acss_test_is_admin']   = true;
$GLOBALS['acss_test_current_user_can'] = true;
$GLOBALS['acss_test_nonce_valid'] = true;
$GLOBALS['acss_test_postmeta']   = [];

final class ACSS_Test_WP_Send_Json_Exception extends RuntimeException {

	/** @var mixed */
	public $payload;

	public int $status_code;

	/**
	 * @param mixed $payload
	 */
	public function __construct( $payload, int $status_code = 200 ) {
		parent::__construct( 'wp_send_json called' );
		$this->payload      = $payload;
		$this->status_code = $status_code;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $key, $default = false ) {
		return $GLOBALS['acss_test_options'][ $key ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( string $key, $value ): bool {
		$GLOBALS['acss_test_options'][ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( string $key ): bool {
		unset( $GLOBALS['acss_test_transients'][ $key ] );
		return true;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( string $hook, ...$args ): void {
		$GLOBALS['acss_test_actions'][] = [
			'hook' => $hook,
			'args' => $args,
		];

		if ( isset( $GLOBALS['acss_test_action_callbacks'][ $hook ] ) ) {
			$GLOBALS['acss_test_action_callbacks'][ $hook ]( ...$args );
		}
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( string $hook, $callback ): void {
		$GLOBALS['acss_test_actions'][] = [
			'hook'     => $hook,
			'callback' => $callback,
		];
	}
}

if ( ! function_exists( 'add_management_page' ) ) {
	function add_management_page( string $page_title, string $menu_title, string $capability, string $menu_slug, $callback ): void {
		$GLOBALS['acss_test_management_pages'][] = compact( 'page_title', 'menu_title', 'capability', 'menu_slug', 'callback' );
	}
}

if ( ! function_exists( 'add_submenu_page' ) ) {
	function add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, $callback ): void {
		$GLOBALS['acss_test_submenu_pages'][] = compact( 'parent_slug', 'page_title', 'menu_title', 'capability', 'menu_slug', 'callback' );
	}
}

if ( ! function_exists( 'maybe_unserialize' ) ) {
	function maybe_unserialize( $data ) {
		return $data;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( int $post_id, string $meta_key, bool $single = false ) {
		$value = $GLOBALS['acss_test_postmeta'][ $post_id ][ $meta_key ] ?? '';

		if ( $single ) {
			return $value;
		}

		return [ $value ];
	}
}

if ( ! function_exists( 'update_post_meta' ) ) {
	function update_post_meta( int $post_id, string $meta_key, $value ): bool {
		$GLOBALS['acss_test_postmeta'][ $post_id ][ $meta_key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( string $file ): string {
		return 'https://example.com/wp-content/plugins/acss3-to-4/';
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( string $handle, string $src, array $deps = [], $ver = false ): void {
		$GLOBALS['acss_test_styles'][ $handle ] = compact( 'src', 'deps', 'ver' );
	}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( string $handle, string $src, array $deps = [], $ver = false, bool $in_footer = false ): void {
		$GLOBALS['acss_test_scripts'][ $handle ] = compact( 'src', 'deps', 'ver', 'in_footer' );
	}
}

if ( ! function_exists( 'wp_localize_script' ) ) {
	function wp_localize_script( string $handle, string $object_name, array $l10n ): void {
		$GLOBALS['acss_test_localized'][ $handle ] = compact( 'object_name', 'l10n' );
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( string $path = '' ): string {
		return 'https://example.com/wp-admin/' . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( string $action ): string {
		return 'test-nonce';
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( string $capability ): bool {
		return (bool) $GLOBALS['acss_test_current_user_can'];
	}
}

if ( ! function_exists( 'check_ajax_referer' ) ) {
	function check_ajax_referer( string $action, $query_arg = false ): bool {
		if ( ! $GLOBALS['acss_test_nonce_valid'] ) {
			throw new ACSS_Test_WP_Send_Json_Exception( [ 'success' => false, 'data' => 'Invalid nonce' ], 403 );
		}

		return true;
	}
}

if ( ! function_exists( 'wp_send_json' ) ) {
	function wp_send_json( $response, int $status_code = 200 ): void {
		throw new ACSS_Test_WP_Send_Json_Exception( $response, $status_code );
	}
}

if ( ! function_exists( 'wp_send_json_error' ) ) {
	function wp_send_json_error( $response = null, ?int $status_code = null ): void {
		throw new ACSS_Test_WP_Send_Json_Exception(
			[
				'success' => false,
				'data'    => $response,
			],
			$status_code ?? 200
		);
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	function is_admin(): bool {
		return (bool) $GLOBALS['acss_test_is_admin'];
	}
}

require_once dirname( __DIR__ ) . '/includes/ACSS_CSS_Transformer.php';
require_once dirname( __DIR__ ) . '/includes/Migrators/ACSS_Settings_Migrator.php';
require_once dirname( __DIR__ ) . '/includes/Migrators/ACSS_Elements_Migrator.php';
require_once dirname( __DIR__ ) . '/includes/Migrators/ACSS_Global_Classes_Migrator.php';
require_once dirname( __DIR__ ) . '/includes/Admin/ACSS_Admin_Page.php';
require_once dirname( __DIR__ ) . '/includes/Admin/ACSS_Ajax_Handler.php';
require_once dirname( __DIR__ ) . '/includes/ACSS_Plugin.php';
require_once dirname( __DIR__ ) . '/includes/ACSS_Updater.php';
