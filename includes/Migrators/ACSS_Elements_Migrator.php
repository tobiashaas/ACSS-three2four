<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACSS_Elements_Migrator {

	private const BATCH_SIZE = 20;

	private const META_KEYS = [
		'_bricks_page_content',
		'_bricks_page_header',
		'_bricks_page_footer',
		'_bricks_page_settings',
	];

	private ACSS_CSS_Transformer $transformer;

	public function __construct( ACSS_CSS_Transformer $transformer ) {
		$this->transformer = $transformer;
	}

	/**
	 * Return the total number of posts that have Bricks page meta.
	 */
	public function get_total(): int {
		global $wpdb;

		$keys  = implode( ', ', array_fill( 0, count( self::META_KEYS ), '%s' ) );
		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key IN ({$keys})",
				self::META_KEYS
			)
		);

		return (int) $total;
	}

	/**
	 * Process one batch of posts starting at $offset.
	 *
	 * @param int $offset Number of posts already processed.
	 * @return array{processed: int, total: int, converted: int, flagged: int, flagged_ids: int[]}
	 */
	public function run_batch( int $offset ): array {
		global $wpdb;

		$total = $this->get_total();
		$keys  = implode( ', ', array_fill( 0, count( self::META_KEYS ), '%s' ) );

		$args     = array_merge( self::META_KEYS, [ self::BATCH_SIZE, $offset ] );
		$post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta}
				 WHERE meta_key IN ({$keys})
				 ORDER BY post_id ASC
				 LIMIT %d OFFSET %d",
				$args
			)
		);

		$converted   = 0;
		$flagged     = 0;
		$flagged_ids = [];

		foreach ( $post_ids as $raw_id ) {
			$post_id      = (int) $raw_id;
			$post_flagged = 0;

			foreach ( self::META_KEYS as $meta_key ) {
				$raw = get_post_meta( $post_id, $meta_key, true );

				if ( empty( $raw ) ) {
					continue;
				}

				$elements = maybe_unserialize( $raw );

				if ( ! is_array( $elements ) ) {
					continue;
				}

				$modified = false;

				foreach ( $elements as &$element ) {
					$element_modified = false;
					$result           = $this->transform_element( $element, $element_modified );

					$converted    += $result['converted'];
					$post_flagged += $result['flagged'];
					$modified      = $modified || $element_modified;
				}
				unset( $element );

				if ( $modified ) {
					update_post_meta( $post_id, $meta_key, $elements );
				}
			}

			if ( $post_flagged > 0 ) {
				$flagged      += $post_flagged;
				$flagged_ids[] = $post_id;
			}
		}

		return [
			'processed'   => $offset + count( $post_ids ),
			'total'       => $total,
			'converted'   => $converted,
			'flagged'     => $flagged,
			'flagged_ids' => $flagged_ids,
		];
	}

	/**
	 * @param array<string, mixed> $element
	 * @param bool                 $modified Set to true when the element tree changes.
	 * @return array{converted: int, flagged: int}
	 */
	private function transform_element( array &$element, bool &$modified ): array {
		$converted = 0;
		$flagged   = 0;

		if ( isset( $element['settings']['_css'] ) && '' !== $element['settings']['_css'] ) {
			$result = $this->transformer->transform( $element['settings']['_css'] );

			if ( $result['converted'] > 0 || $result['flagged'] > 0 ) {
				$element['settings']['_css'] = $result['css'];
				$converted                  += $result['converted'];
				$flagged                    += $result['flagged'];
				$modified                    = true;
			}
		}

		if ( isset( $element['children'] ) && is_array( $element['children'] ) ) {
			foreach ( $element['children'] as &$child ) {
				if ( ! is_array( $child ) ) {
					continue;
				}

				$child_result = $this->transform_element( $child, $modified );
				$converted   += $child_result['converted'];
				$flagged     += $child_result['flagged'];
			}
			unset( $child );
		}

		return [
			'converted' => $converted,
			'flagged'   => $flagged,
		];
	}
}
