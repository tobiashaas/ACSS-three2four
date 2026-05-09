<?php

use PHPUnit\Framework\TestCase;

final class ACSS_Plugin_Test extends TestCase {

	protected function setUp(): void {
		$GLOBALS['acss_test_is_admin'] = true;
	}

	public function test_boot_registers_components_only_in_admin(): void {
		$admin_page   = new class() extends ACSS_Admin_Page {
			public int $register_calls = 0;

			public function register(): void {
				++$this->register_calls;
			}
		};
		$ajax_handler = new class(
			$this->createMock( ACSS_Settings_Migrator::class ),
			$this->createMock( ACSS_Elements_Migrator::class ),
			$this->createMock( ACSS_Global_Classes_Migrator::class )
		) extends ACSS_Ajax_Handler {
			public int $register_calls = 0;

			public function register(): void {
				++$this->register_calls;
			}
		};

		$plugin = new ACSS_Plugin( $admin_page, $ajax_handler );

		$GLOBALS['acss_test_is_admin'] = false;
		$plugin->boot();
		$this->assertSame( 0, $admin_page->register_calls );
		$this->assertSame( 0, $ajax_handler->register_calls );

		$GLOBALS['acss_test_is_admin'] = true;
		$plugin->boot();
		$this->assertSame( 1, $admin_page->register_calls );
		$this->assertSame( 1, $ajax_handler->register_calls );
	}
}
