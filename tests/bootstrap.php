<?php

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ );
}

$GLOBALS['acss_test_options']    = [];
$GLOBALS['acss_test_transients'] = [];
$GLOBALS['acss_test_actions']    = [];
$GLOBALS['acss_test_postmeta']   = [];

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

require_once dirname( __DIR__ ) . '/includes/ACSS_CSS_Transformer.php';
require_once dirname( __DIR__ ) . '/includes/Migrators/ACSS_Settings_Migrator.php';
require_once dirname( __DIR__ ) . '/includes/Migrators/ACSS_Elements_Migrator.php';
require_once dirname( __DIR__ ) . '/includes/Migrators/ACSS_Global_Classes_Migrator.php';
