<?php

if ( defined( 'ABSPATH' ) && ! defined( 'ACSS3TO4_FILE' ) ) {
	exit;
}

class ACSS_CSS_Transformer {

	private const COLORS = [
		'primary', 'secondary', 'tertiary', 'accent',
		'base', 'neutral', 'info', 'success', 'warning', 'danger',
	];

	private const SIZE_MAP = [
		'xs'  => '10',
		's'   => '30',
		'm'   => '50',
		'l'   => '70',
		'xl'  => '80',
		'xxl' => '90',
	];

	/**
	 * Transform ACSS3 CSS references to ACSS4 equivalents.
	 *
	 * @param string $css Raw CSS string (declaration block or full rule set).
	 * @return array{css: string, converted: int, flagged: int}
	 */
	public function transform( string $css ): array {
		if ( '' === $css ) {
			return [ 'css' => '', 'converted' => 0, 'flagged' => 0 ];
		}

		$converted = 0;
		$flagged   = 0;

		$css = $this->convert_hsl_tuples( $css, $converted );
		$css = $this->convert_bare_hsl_vars( $css, $converted );
		$css = $this->convert_hsl_component_calls( $css, $converted );
		$css = $this->convert_trans_vars( $css, $converted );
		$css = $this->convert_medium_vars( $css, $converted );
		$css = $this->convert_texture_vars( $css, $converted );
		$css = $this->convert_size_vars( $css, $converted );
		$css = $this->flag_standalone_channel_vars( $css, $flagged );

		return [ 'css' => $css, 'converted' => $converted, 'flagged' => $flagged ];
	}

	private function color_pattern(): string {
		return implode( '|', self::COLORS );
	}

	private function convert_hsl_tuples( string $css, int &$converted ): string {
		$c       = $this->color_pattern();
		$pattern = '/hsl\(\s*var\(\s*--(' . $c . ')-hsl\s*\)\s*(?:\/\s*([^()]*(?:\([^)]*\)[^()]*)*))?\s*\)/i';

		return preg_replace_callback(
			$pattern,
			function ( array $m ) use ( &$converted ): string {
				$color = strtolower( $m[1] );
				$alpha = isset( $m[2] ) ? trim( $m[2] ) : '';
				++$converted;

				if ( '' !== $alpha ) {
					return 'color-mix(in oklab, var(--' . $color . ') ' . $this->alpha_to_percentage( $alpha ) . ', transparent)';
				}

				return 'var(--' . $color . ')';
			},
			$css
		) ?? $css;
	}

	private function convert_bare_hsl_vars( string $css, int &$converted ): string {
		$c = $this->color_pattern();

		return preg_replace_callback(
			'/var\(\s*--(' . $c . ')-hsl\s*\)/i',
			function ( array $m ) use ( &$converted ): string {
				++$converted;
				return 'var(--' . strtolower( $m[1] ) . ')';
			},
			$css
		) ?? $css;
	}

	private function convert_hsl_component_calls( string $css, int &$converted ): string {
		$c       = $this->color_pattern();
		$pattern = '/hsla?\(\s*var\(\s*--(' . $c . ')-h\s*\)\s*,\s*var\(\s*--\1-s\s*\)%\s*,\s*var\(\s*--\1-l\s*\)%(?:\s*,\s*([^()]*(?:\([^)]*\)[^()]*)*))?s*\)/i';

		return preg_replace_callback(
			$pattern,
			function ( array $m ) use ( &$converted ): string {
				$color = strtolower( $m[1] );
				$alpha = isset( $m[2] ) ? trim( $m[2] ) : '';
				++$converted;

				if ( '' !== $alpha ) {
					return 'color-mix(in oklch, var(--' . $color . ') ' . $this->alpha_to_percentage( $alpha ) . ', transparent)';
				}

				return 'var(--' . $color . ')';
			},
			$css
		) ?? $css;
	}

	private function convert_trans_vars( string $css, int &$converted ): string {
		$c = $this->color_pattern();

		return preg_replace_callback(
			'/var\(\s*--(' . $c . '(?:-[a-z-]+)?)-trans-([0-9]{1,3})\s*\)/i',
			function ( array $m ) use ( &$converted ): string {
				++$converted;
				$token = strtolower( $m[1] );
				$pct   = max( 0, min( 100, (int) $m[2] ) );
				return 'color-mix(in oklch, var(--' . $token . ') ' . $pct . '%, transparent)';
			},
			$css
		) ?? $css;
	}

	private function convert_medium_vars( string $css, int &$converted ): string {
		$c = $this->color_pattern();

		return preg_replace_callback(
			'/var\(\s*--(' . $c . ')-medium\s*\)/i',
			function ( array $m ) use ( &$converted ): string {
				++$converted;
				return 'var(--' . strtolower( $m[1] ) . '-hover)';
			},
			$css
		) ?? $css;
	}

	private function convert_texture_vars( string $css, int &$converted ): string {
		$css = preg_replace_callback(
			'/var\(\s*--texture-([a-z0-9_-]+)\s*\)/i',
			function ( array $m ) use ( &$converted ): string {
				++$converted;
				return 'var(--surface-' . strtolower( $m[1] ) . ')';
			},
			$css
		) ?? $css;

		return preg_replace_callback(
			'/--texture-([a-z0-9_-]+)\s*:/i',
			function ( array $m ) use ( &$converted ): string {
				++$converted;
				return '--surface-' . strtolower( $m[1] ) . ':';
			},
			$css
		) ?? $css;
	}

	private function convert_size_vars( string $css, int &$converted ): string {
		foreach ( self::SIZE_MAP as $old => $new ) {
			$css = preg_replace_callback(
				'/var\(\s*--(width|height)-' . preg_quote( $old, '/' ) . '\s*\)/i',
				function ( array $m ) use ( $new, &$converted ): string {
					++$converted;
					return 'var(--' . strtolower( $m[1] ) . '-' . $new . ')';
				},
				$css
			) ?? $css;
		}

		return $css;
	}

	private function flag_standalone_channel_vars( string $css, int &$flagged ): string {
		$c        = $this->color_pattern();
		$channels = [ 'h', 's', 'l', 'rgb', 'r', 'g', 'b', 'hex' ];

		foreach ( $channels as $channel ) {
			$css = preg_replace_callback(
				'/var\(\s*--(' . $c . ')-' . preg_quote( $channel, '/' ) . '\s*\)/i',
				function ( array $m ) use ( $channel, &$flagged ): string {
					++$flagged;
					$original = $m[0];
					return '/* ACSS3: needs manual review: --' . strtolower( $m[1] ) . '-' . $channel . ' */ ' . $original;
				},
				$css
			) ?? $css;
		}

		return $css;
	}

	private function alpha_to_percentage( string $alpha ): string {
		$value = trim( $alpha );

		if ( '' === $value ) {
			return '100%';
		}

		if ( preg_match( '/^\d+(\.\d+)?%$/', $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			$float = (float) $value;
			if ( $float <= 1 ) {
				$float *= 100;
			}

			$float     = max( 0, min( 100, $float ) );
			$formatted = rtrim( rtrim( sprintf( '%.4f', $float ), '0' ), '.' );
			return $formatted . '%';
		}

		// Pass calc() expressions through unchanged to avoid double-wrapping.
		if ( preg_match( '/^calc\s*\(/i', $value ) ) {
			return $value;
		}

		return 'calc(' . $value . ' * 100%)';
	}
}
