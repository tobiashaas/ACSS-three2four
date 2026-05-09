<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACSS_Plugin {

	private ACSS_Admin_Page   $admin_page;
	private ACSS_Ajax_Handler $ajax_handler;

	public function __construct( ACSS_Admin_Page $admin_page, ACSS_Ajax_Handler $ajax_handler ) {
		$this->admin_page   = $admin_page;
		$this->ajax_handler = $ajax_handler;
	}

	public function boot(): void {
		if ( is_admin() ) {
			$this->admin_page->register();
			$this->ajax_handler->register();
		}
	}
}
