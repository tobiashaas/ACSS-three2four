<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACSS_Global_Classes_Migrator {

	private const MAX_SAMPLES_PER_DETAIL = 5;

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
	 * @return array{updated_count: int, converted: int, flagged: int, details: array<int, array<string, mixed>>}
	 */
	public function run(): array {
		$classes = get_option( 'bricks_global_classes', [] );

		if ( ! is_array( $classes ) ) {
			return [
				'updated_count' => 0,
				'converted'     => 0,
				'flagged'       => 0,
				'details'       => [],
			];
		}

		$updated = 0;
		$converted = 0;
		$flagged   = 0;
		$details   = [];

		foreach ( $classes as $index => &$class ) {
			if ( ! $this->is_acss_class( $class ) ) {
				continue;
			}

			$detail = [
				'class_name' => (string) ( $class['name'] ?? '' ),
				'renamed'    => false,
				'converted'  => 0,
				'flagged'    => 0,
				'samples'    => [],
			];

			if ( isset( $class['name'] ) ) {
				$new_name = $this->convert_class_name( (string) $class['name'] );

				if ( $new_name !== $class['name'] ) {
					$class['name'] = $new_name;
					++$updated;
					$detail['renamed'] = true;
				}
			}

			$detail['class_name'] = (string) ( $class['name'] ?? $detail['class_name'] );

			$modified = false;
			$result   = $this->transform_value( $class, '', $modified );

			$converted += $result['converted'];
			$flagged   += $result['flagged'];

			if ( $modified || $detail['renamed'] ) {
				$detail['converted'] = $result['converted'];
				$detail['flagged']   = $result['flagged'];
				$detail['samples']   = $result['samples'];
				$details[]           = $detail;
				$classes[ $index ]   = $class;
			}
		}
		unset( $class );

		update_option( 'bricks_global_classes', $classes );

		return [
			'updated_count' => $updated,
			'converted'     => $converted,
			'flagged'       => $flagged,
			'details'       => $details,
		];
	}

	/**
	 * @param mixed  $value
	 * @param string $path
	 * @param bool   $modified
	 * @return array{converted: int, flagged: int, samples: array<int, array<string, string>>}
	 */
	private function transform_value( &$value, string $path, bool &$modified ): array {
		$converted = 0;
		$flagged   = 0;
		$samples   = [];

		if ( is_string( $value ) && '' !== $value ) {
			$result = $this->transformer->transform( $value );

			if ( $result['converted'] > 0 || $result['flagged'] > 0 ) {
				$original  = $value;
				$value     = $result['css'];
				$converted = $result['converted'];
				$flagged   = $result['flagged'];
				$modified  = true;
				$samples[] = [
					'path'   => ltrim( $path, '.' ),
					'before' => $original,
					'after'  => $result['css'],
				];
			}

			return [
				'converted' => $converted,
				'flagged'   => $flagged,
				'samples'   => $samples,
			];
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $key => &$child ) {
				$child_path   = '' === $path ? (string) $key : $path . '.' . $key;
				$child_result = $this->transform_value( $child, $child_path, $modified );
				$converted   += $child_result['converted'];
				$flagged     += $child_result['flagged'];
				$samples      = array_merge( $samples, $child_result['samples'] );

				if ( count( $samples ) > self::MAX_SAMPLES_PER_DETAIL ) {
					$samples = array_slice( $samples, 0, self::MAX_SAMPLES_PER_DETAIL );
				}
			}
			unset( $child );
		}

		return [
			'converted' => $converted,
			'flagged'   => $flagged,
			'samples'   => $samples,
		];
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
