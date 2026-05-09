<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACSS_Ajax_Handler {

	private ACSS_Settings_Migrator       $settings_migrator;
	private ACSS_Elements_Migrator       $elements_migrator;
	private ACSS_Global_Classes_Migrator $classes_migrator;

	public function __construct(
		ACSS_Settings_Migrator $settings_migrator,
		ACSS_Elements_Migrator $elements_migrator,
		ACSS_Global_Classes_Migrator $classes_migrator
	) {
		$this->settings_migrator = $settings_migrator;
		$this->elements_migrator = $elements_migrator;
		$this->classes_migrator  = $classes_migrator;
	}

	public function register(): void {
		add_action( 'wp_ajax_acss3to4_step1', [ $this, 'step1' ] );
		add_action( 'wp_ajax_acss3to4_step2', [ $this, 'step2' ] );
		add_action( 'wp_ajax_acss3to4_step3', [ $this, 'step3' ] );
	}

	public function step1(): void {
		$this->verify_request();
		wp_send_json( $this->settings_migrator->run() );
	}

	public function step2(): void {
		$this->verify_request();
		$offset = max( 0, (int) ( $_POST['offset'] ?? 0 ) );
		wp_send_json( $this->elements_migrator->run_batch( $offset ) );
	}

	public function step3(): void {
		$this->verify_request();
		wp_send_json( $this->classes_migrator->run() );
	}

	private function verify_request(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		check_ajax_referer( 'acss3to4_nonce', 'nonce' );
	}
}
