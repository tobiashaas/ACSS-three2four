<?php

use PHPUnit\Framework\TestCase;

final class ACSS_Admin_Page_Test extends TestCase {

	protected function setUp(): void {
		$GLOBALS['acss_test_actions']          = [];
		$GLOBALS['acss_test_management_pages'] = [];
		$GLOBALS['acss_test_styles']           = [];
		$GLOBALS['acss_test_scripts']          = [];
		$GLOBALS['acss_test_localized']        = [];
	}

	public function test_register_adds_admin_hooks(): void {
		$page = new ACSS_Admin_Page();

		$page->register();

		$this->assertCount( 2, $GLOBALS['acss_test_actions'] );
		$this->assertSame( 'admin_menu', $GLOBALS['acss_test_actions'][0]['hook'] );
		$this->assertSame( 'admin_enqueue_scripts', $GLOBALS['acss_test_actions'][1]['hook'] );
	}

	public function test_enqueue_assets_only_on_plugin_screen(): void {
		$page = new ACSS_Admin_Page();

		$page->enqueue_assets( 'dashboard_page_other' );

		$this->assertSame( [], $GLOBALS['acss_test_styles'] );
		$this->assertSame( [], $GLOBALS['acss_test_scripts'] );
	}

	public function test_enqueue_assets_uses_shared_plugin_version(): void {
		$page = new ACSS_Admin_Page();

		$page->enqueue_assets( 'tools_page_acss3-to-4' );

		$this->assertSame( ACSS3TO4_VERSION, $GLOBALS['acss_test_styles']['acss3to4-admin']['ver'] ?? null );
		$this->assertSame( ACSS3TO4_VERSION, $GLOBALS['acss_test_scripts']['acss3to4-admin']['ver'] ?? null );
		$this->assertSame( 'test-nonce', $GLOBALS['acss_test_localized']['acss3to4-admin']['l10n']['nonce'] ?? null );
	}
}
