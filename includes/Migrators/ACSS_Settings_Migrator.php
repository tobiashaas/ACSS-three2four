<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACSS_Settings_Migrator {

	/**
	 * Trigger ACSS4's internal migration chain.
	 *
	 * Sets db_version to 2.0.0 so the full ACSS4 migration chain runs
	 * (HSL→OKLCH, key renames, CSS regeneration), then stamps the current
	 * ACSS4 version to prevent re-runs on subsequent admin_init.
	 *
	 * @return array{success: bool, message: string}
	 */
	public function run(): array {
		$target_version = $this->resolve_target_version();

		if ( null === $target_version ) {
			return [ 'success' => false, 'message' => 'ACSS4 not found — no supported ACSS4 version could be detected.' ];
		}

		$previous_version = (string) get_option( 'automatic_css_db_version', '' );

		try {
			delete_transient( 'automaticcss_database_upgrade_lock' );
			update_option( 'automatic_css_db_version', '2.0.0' );

			do_action( 'automaticcss_update_plugin_start', $target_version, '2.0.0' );

			update_option( 'automatic_css_db_version', $target_version );
		} catch ( \Throwable $e ) {
			if ( false !== stripos( get_class( $e ), 'CSS_Generation' ) ) {
				update_option( 'automatic_css_db_version', $target_version );

				return [
					'success' => true,
					'message' => 'ACSS settings migrated. CSS regeneration failed — please regenerate manually from the ACSS dashboard.',
				];
			}

			update_option( 'automatic_css_db_version', $previous_version );

			return [ 'success' => false, 'message' => 'Error: ' . $e->getMessage() ];
		}

		return [ 'success' => true, 'message' => 'ACSS settings migrated successfully (ACSS4 migration chain executed).' ];
	}

	private function resolve_target_version(): ?string {
		if ( defined( 'ACSS_PLUGIN_VERSION' ) ) {
			return ACSS_PLUGIN_VERSION;
		}

		$db_version = (string) get_option( 'automatic_css_db_version', '' );

		if ( $this->is_supported_acss4_version( $db_version ) ) {
			return $db_version;
		}

		return null;
	}

	private function is_supported_acss4_version( string $version ): bool {
		$major = (int) strtok( ltrim( $version ), '.' );

		return $major >= 4;
	}
}
