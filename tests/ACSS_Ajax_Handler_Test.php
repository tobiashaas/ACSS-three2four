<?php

use PHPUnit\Framework\TestCase;

final class ACSS_Ajax_Handler_Test extends TestCase {

	protected function setUp(): void {
		$GLOBALS['acss_test_current_user_can'] = true;
		$GLOBALS['acss_test_nonce_valid']      = true;
		$_POST                                 = [];
	}

	public function test_step2_sanitizes_negative_offset_before_running_batch(): void {
		$settings = $this->createMock( ACSS_Settings_Migrator::class );
		$classes  = $this->createMock( ACSS_Global_Classes_Migrator::class );
		$elements = $this->createMock( ACSS_Elements_Migrator::class );

		$elements
			->expects( $this->once() )
			->method( 'run_batch' )
			->with( 0 )
			->willReturn(
				[
					'processed'   => 0,
					'total'       => 0,
					'converted'   => 0,
					'flagged'     => 0,
					'flagged_ids' => [],
				]
			);

		$handler = new ACSS_Ajax_Handler( $settings, $elements, $classes );
		$_POST['offset'] = '-25';

		$this->expectException( ACSS_Test_WP_Send_Json_Exception::class );
		$handler->step2();
	}

	public function test_step1_rejects_unauthorized_requests(): void {
		$settings = $this->createMock( ACSS_Settings_Migrator::class );
		$elements = $this->createMock( ACSS_Elements_Migrator::class );
		$classes  = $this->createMock( ACSS_Global_Classes_Migrator::class );
		$handler  = new ACSS_Ajax_Handler( $settings, $elements, $classes );

		$GLOBALS['acss_test_current_user_can'] = false;

		try {
			$handler->step1();
			$this->fail( 'Expected wp_send_json_error exception was not thrown.' );
		} catch ( ACSS_Test_WP_Send_Json_Exception $e ) {
			$this->assertSame( 403, $e->status_code );
			$this->assertFalse( $e->payload['success'] );
			$this->assertSame( 'Unauthorized', $e->payload['data'] );
		}
	}
}
