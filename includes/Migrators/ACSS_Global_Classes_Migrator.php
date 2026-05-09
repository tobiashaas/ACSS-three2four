<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACSS_Global_Classes_Migrator {

	private const SIZE_MAP = [
		'xs'  => '10',
		's'   => '30',
		'm'   => '50',
		'l'   => '70',
		'xl'  => '80',
		'xxl' => '90',
	];

	private ACSS_CSS_Transformer $transformer;

	public function __construct( ACSS_CSS_Transformer $transformer ) {
		$this->transformer = $transformer;
	}

	/**
	 * Update bricks_global_classes: rename t-shirt sizes and transform CSS.
	 *
	 * @return array{updated_count: int}
	 */
	public function run(): array {
		$classes = get_option( 'bricks_global_classes', [] );

		if ( ! is_array( $classes ) ) {
			return [ 'updated_count' => 0 ];
		}

		$updated = 0;

		foreach ( $classes as &$class ) {
			if ( ! $this->is_acss_class( $class ) ) {
				continue;
			}

			if ( isset( $class['name'] ) ) {
				$new_name = $this->convert_class_name( (string) $class['name'] );

				if ( $new_name !== $class['name'] ) {
					$class['name'] = $new_name;
					++$updated;
				}
			}

			if ( isset( $class['settings']['css'] ) && '' !== $class['settings']['css'] ) {
				$result = $this->transformer->transform( $class['settings']['css'] );

				if ( $result['converted'] > 0 ) {
					$class['settings']['css'] = $result['css'];
				}
			}
		}
		unset( $class );

		update_option( 'bricks_global_classes', $classes );

		return [ 'updated_count' => $updated ];
	}

	private function is_acss_class( array $class ): bool {
		$category = strtolower( trim( (string) ( $class['category'] ?? '' ) ) );

		if ( 'acss' === $category ) {
			return true;
		}

		$name = trim( (string) ( $class['name'] ?? '' ) );

		return str_starts_with( $name, 'acss_import_' );
	}

	private function convert_class_name( string $name ): string {
		foreach ( self::SIZE_MAP as $old => $new ) {
			$name = (string) preg_replace(
				'/--' . preg_quote( $old, '/' ) . '(?=[^a-z]|$)/',
				'--' . $new,
				$name
			);
		}

		return $name;
	}
}
