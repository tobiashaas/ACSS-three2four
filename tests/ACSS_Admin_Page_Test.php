<?php

use PHPUnit\Framework\TestCase;

final class ACSS_Admin_Page_Test extends TestCase {

	protected function setUp(): void {
		$GLOBALS['acss_test_actions']          = [];
		$GLOBALS['acss_test_management_pages'] = [];
		$GLOBALS['acss_test_submenu_pages']    = [];
		$GLOBALS['acss_test_styles']           = [];
		$GLOBALS['acss_test_scripts']          = [];
		$GLOBALS['acss_test_localized']        = [];
		$GLOBALS['acss_test_options']          = [];
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

	public function test_add_menu_page_uses_acss_submenu_when_acss_is_active(): void {
		$page = new class() extends ACSS_Admin_Page {
			protected function is_acss_active(): bool {
				return true;
			}
		};

		$page->add_menu_page();

		$this->assertCount( 1, $GLOBALS['acss_test_submenu_pages'] );
		$this->assertSame( 'automatic-css', $GLOBALS['acss_test_submenu_pages'][0]['parent_slug'] );
		$this->assertSame( [], $GLOBALS['acss_test_management_pages'] );
	}

	public function test_add_menu_page_falls_back_to_tools_when_acss_is_missing(): void {
		$page = new class() extends ACSS_Admin_Page {
			protected function is_acss_active(): bool {
				return false;
			}
		};

		$page->add_menu_page();

		$this->assertCount( 1, $GLOBALS['acss_test_management_pages'] );
		$this->assertSame( [], $GLOBALS['acss_test_submenu_pages'] );
	}

	public function test_render_page_allows_acss_four_release_candidate_versions(): void {
		$GLOBALS['acss_test_options']['automatic_css_db_version'] = '4.0.0-rc-1';

		$page = new class() extends ACSS_Admin_Page {
			protected function is_acss_active(): bool {
				return true;
			}

			public function render_for_test(): string {
				ob_start();
				$this->render_page();
				return (string) ob_get_clean();
			}
		};

		$html = $page->render_for_test();

		$this->assertStringContainsString( 'Start Migration', $html );
		$this->assertStringNotContainsString( 'Please update the ACSS plugin to version 4 first', $html );
	}

	public function test_render_page_blocks_acss_three_versions(): void {
		$GLOBALS['acss_test_options']['automatic_css_db_version'] = '3.2.9';

		$page = new class() extends ACSS_Admin_Page {
			protected function is_acss_active(): bool {
				return true;
			}

			public function render_for_test(): string {
				ob_start();
				$this->render_page();
				return (string) ob_get_clean();
			}
		};

		$html = $page->render_for_test();

		$this->assertStringContainsString( 'Please update the ACSS plugin to version 4 first', $html );
	}
}
