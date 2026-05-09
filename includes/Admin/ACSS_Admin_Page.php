<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACSS_Admin_Page {

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function add_menu_page(): void {
		add_management_page(
			'ACSS3 to ACSS4',
			'ACSS3 to ACSS4',
			'manage_options',
			'acss3-to-4',
			[ $this, 'render_page' ]
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( 'tools_page_acss3-to-4' !== $hook ) {
			return;
		}

		$base = plugin_dir_url( ACSS3TO4_FILE );
		wp_enqueue_style( 'acss3to4-admin', $base . 'assets/admin.css', [], ACSS3TO4_VERSION );
		wp_enqueue_script( 'acss3to4-admin', $base . 'assets/admin.js', [], ACSS3TO4_VERSION, true );
		wp_localize_script(
			'acss3to4-admin',
			'acss3to4',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'acss3to4_nonce' ),
				'acssUrl' => admin_url( 'admin.php?page=automatic-css' ),
			]
		);
	}

	public function render_page(): void {
		$guard   = $this->check_prerequisites();
		$version = get_option( 'automatic_css_db_version', 'unknown' );
		$bricks  = defined( 'BRICKS_VERSION' ) ? BRICKS_VERSION : 'not found';
		?>
		<div class="wrap" id="acss3to4-wrap">
			<h1>ACSS3 to ACSS4 Migration</h1>

			<?php if ( $guard['blocking'] ) : ?>
				<div class="notice notice-error">
					<p><?php echo esc_html( $guard['message'] ); ?></p>
				</div>
			<?php else : ?>

				<?php if ( '' !== $guard['warning'] ) : ?>
					<div class="notice notice-warning">
						<p><?php echo esc_html( $guard['warning'] ); ?></p>
					</div>
				<?php endif; ?>

				<div class="notice notice-warning">
					<p><strong>⚠ Create a database backup before starting.</strong></p>
				</div>

				<p>
					<strong>Detected:</strong>
					ACSS <?php echo esc_html( $version ); ?> &nbsp;•&nbsp;
					Bricks <?php echo esc_html( $bricks ); ?>
				</p>

				<button id="acss3to4-start" class="button button-primary">Start Migration</button>

				<div id="acss3to4-steps">
					<div class="acss3to4-step" data-step="step1">
						<span class="step-status">○</span>
						Step 1 — ACSS Settings
					</div>
					<div class="acss3to4-step" data-step="step2">
						<span class="step-status">○</span>
						Step 2 — Bricks Elements
						<span class="step-progress"></span>
					</div>
					<div class="acss3to4-step" data-step="step3">
						<span class="step-status">○</span>
						Step 3 — Global Classes
					</div>
				</div>

				<div id="acss3to4-log" hidden>
					<h3>Log</h3>
					<ul id="acss3to4-log-list"></ul>
				</div>

				<div id="acss3to4-done" hidden>
					<a id="acss3to4-regen" href="#" class="button" target="_blank">
						Regenerate ACSS CSS
					</a>
				</div>

			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * @return array{blocking: bool, message: string, warning: string}
	 */
	private function check_prerequisites(): array {
		$acss_active = defined( 'ACSS_PLUGIN_FILE' ) || class_exists( 'Automatic_CSS\Plugin' );

		if ( ! $acss_active ) {
			return [
				'blocking' => true,
				'message'  => 'ACSS not found. Please install and activate ACSS4 first.',
				'warning'  => '',
			];
		}

		$version = (string) get_option( 'automatic_css_db_version', '' );

		if ( '' !== $version && version_compare( $version, '4.0.0', '<' ) ) {
			return [
				'blocking' => true,
				'message'  => 'ACSS3 detected (version ' . $version . '). Please update the ACSS plugin to version 4 first, then return here.',
				'warning'  => '',
			];
		}

		$warning = '';

		if ( ! defined( 'BRICKS_VERSION' ) && ! class_exists( 'Bricks\Database' ) ) {
			$warning = 'Bricks not found. Step 1 will run; Steps 2 & 3 will be skipped automatically.';
		}

		return [
			'blocking' => false,
			'message'  => '',
			'warning'  => $warning,
		];
	}
}
