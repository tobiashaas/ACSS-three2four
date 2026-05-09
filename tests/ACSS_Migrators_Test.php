<?php

use PHPUnit\Framework\TestCase;

final class ACSS_CSS_Generation_Exception extends RuntimeException {}

final class ACSS_Migrators_Test extends TestCase {

	protected function setUp(): void {
		$GLOBALS['acss_test_options']          = [];
		$GLOBALS['acss_test_transients']       = [];
		$GLOBALS['acss_test_actions']          = [];
		$GLOBALS['acss_test_action_callbacks'] = [];
		$GLOBALS['acss_test_postmeta']         = [];
	}

	public function test_settings_migrator_keeps_previous_db_version_on_hard_failure(): void {
		if ( ! defined( 'ACSS_PLUGIN_VERSION' ) ) {
			define( 'ACSS_PLUGIN_VERSION', '4.0.1' );
		}

		update_option( 'automatic_css_db_version', '4.0.0' );
		$GLOBALS['acss_test_action_callbacks']['automaticcss_update_plugin_start'] = static function (): void {
			throw new RuntimeException( 'Boom' );
		};

		$migrator = new ACSS_Settings_Migrator();
		$result   = $migrator->run();

		$this->assertFalse( $result['success'] );
		$this->assertSame( '4.0.0', get_option( 'automatic_css_db_version' ) );
	}

	public function test_settings_migrator_only_treats_css_generation_exception_as_soft_failure(): void {
		if ( ! defined( 'ACSS_PLUGIN_VERSION' ) ) {
			define( 'ACSS_PLUGIN_VERSION', '4.0.1' );
		}

		update_option( 'automatic_css_db_version', '4.0.0' );
		$GLOBALS['acss_test_action_callbacks']['automaticcss_update_plugin_start'] = static function (): void {
			throw new RuntimeException( 'CSS parsing failed unexpectedly.' );
		};

		$migrator = new ACSS_Settings_Migrator();
		$result   = $migrator->run();

		$this->assertFalse( $result['success'] );
		$this->assertSame( '4.0.0', get_option( 'automatic_css_db_version' ) );
	}

	public function test_settings_migrator_keeps_success_for_css_generation_exception(): void {
		if ( ! defined( 'ACSS_PLUGIN_VERSION' ) ) {
			define( 'ACSS_PLUGIN_VERSION', '4.0.1' );
		}

		update_option( 'automatic_css_db_version', '4.0.0' );
		$GLOBALS['acss_test_action_callbacks']['automaticcss_update_plugin_start'] = static function (): void {
			throw new ACSS_CSS_Generation_Exception( 'Generation failed.' );
		};

		$migrator = new ACSS_Settings_Migrator();
		$result   = $migrator->run();

		$this->assertTrue( $result['success'] );
		$this->assertSame( '4.0.1', get_option( 'automatic_css_db_version' ) );
	}

	public function test_elements_migrator_updates_nested_css_blocks(): void {
		$transformer = new ACSS_CSS_Transformer();
		$migrator    = new ACSS_Elements_Migrator( $transformer );

		$GLOBALS['wpdb'] = new class() {
			public string $postmeta = 'wp_postmeta';

			public function prepare( string $query, ...$args ): string {
				if ( 1 === count( $args ) && is_array( $args[0] ) ) {
					$args = $args[0];
				}

				return vsprintf( str_replace( '%s', "'%s'", $query ), $args );
			}

			public function get_var( string $query ): string {
				return str_contains( $query, 'COUNT(DISTINCT post_id)' ) ? '1' : '0';
			}

			public function get_col( string $query ): array {
				return str_contains( $query, 'LIMIT 20 OFFSET 0' ) ? [ 123 ] : [];
			}
		};

		$GLOBALS['acss_test_postmeta'][123]['_bricks_page_content'] = [
			[
				'name'     => 'section',
				'children' => [
					[
						'name'     => 'heading',
						'settings' => [
							'_css' => 'color: var(--primary-hsl);',
						],
					],
				],
			],
		];

		$result = $migrator->run_batch( 0 );
		$saved  = $GLOBALS['acss_test_postmeta'][123]['_bricks_page_content'];

		$this->assertSame( 1, $result['converted'] );
		$this->assertStringContainsString( 'var(--primary)', $saved[0]['children'][0]['settings']['_css'] );
	}

	public function test_global_classes_migrator_persists_flagged_css_comments(): void {
		$transformer = new ACSS_CSS_Transformer();
		$migrator    = new ACSS_Global_Classes_Migrator( $transformer );

		update_option(
			'bricks_global_classes',
			[
				[
					'name'     => 'acss_import_filter',
					'category' => 'acss',
					'settings' => [
						'css' => 'filter: hue-rotate(calc(var(--primary-h) * 1deg));',
					],
				],
			]
		);

		$migrator->run();

		$saved = get_option( 'bricks_global_classes' );

		$this->assertStringContainsString( 'needs manual review', $saved[0]['settings']['css'] );
	}
}
