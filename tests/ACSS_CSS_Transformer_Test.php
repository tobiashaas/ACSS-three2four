<?php

use PHPUnit\Framework\TestCase;

class ACSS_CSS_Transformer_Test extends TestCase {

	private ACSS_CSS_Transformer $t;

	protected function setUp(): void {
		$this->t = new ACSS_CSS_Transformer();
	}

	public function test_empty_returns_unchanged(): void {
		$r = $this->t->transform( '' );
		$this->assertSame( '', $r['css'] );
		$this->assertSame( 0, $r['converted'] );
		$this->assertSame( 0, $r['flagged'] );
	}

	public function test_unrelated_css_untouched(): void {
		$css = 'display: flex; color: red;';
		$r   = $this->t->transform( $css );
		$this->assertSame( $css, $r['css'] );
		$this->assertSame( 0, $r['converted'] );
		$this->assertSame( 0, $r['flagged'] );
	}

	public function test_hsl_tuple_without_alpha(): void {
		$r = $this->t->transform( 'color: hsl(var(--primary-hsl));' );
		$this->assertStringContainsString( 'var(--primary)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_hsl_tuple_with_decimal_alpha(): void {
		$r = $this->t->transform( 'color: hsl(var(--primary-hsl) / 0.5);' );
		$this->assertStringContainsString( 'color-mix(in oklab, var(--primary) 50%, transparent)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_hsl_tuple_with_percent_alpha(): void {
		$r = $this->t->transform( 'color: hsl(var(--accent-hsl) / 20%);' );
		$this->assertStringContainsString( 'color-mix(in oklab, var(--accent) 20%, transparent)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_bare_hsl_var(): void {
		$r = $this->t->transform( 'background: var(--accent-hsl);' );
		$this->assertStringContainsString( 'var(--accent)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_hsl_component_call_without_alpha(): void {
		$r = $this->t->transform( 'color: hsl(var(--primary-h), var(--primary-s)%, var(--primary-l)%);' );
		$this->assertStringContainsString( 'var(--primary)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
		$this->assertSame( 0, $r['flagged'] );
	}

	public function test_hsl_component_call_with_trailing_whitespace_before_paren(): void {
		$r = $this->t->transform( 'color: hsl(var(--primary-h), var(--primary-s)%, var(--primary-l)% );' );
		$this->assertStringContainsString( 'var(--primary)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
		$this->assertSame( 0, $r['flagged'] );
	}

	public function test_hsla_component_call_with_alpha(): void {
		$r = $this->t->transform( 'color: hsla(var(--primary-h), var(--primary-s)%, var(--primary-l)%, 0.3);' );
		$this->assertStringContainsString( 'color-mix(in oklch, var(--primary) 30%, transparent)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
		$this->assertSame( 0, $r['flagged'] );
	}

	public function test_trans_var(): void {
		$r = $this->t->transform( 'background: var(--primary-trans-20);' );
		$this->assertStringContainsString( 'color-mix(in oklch, var(--primary) 20%, transparent)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_medium_var(): void {
		$r = $this->t->transform( 'color: var(--primary-medium);' );
		$this->assertStringContainsString( 'var(--primary-hover)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_texture_usage_var(): void {
		$r = $this->t->transform( 'background: var(--texture-card);' );
		$this->assertStringContainsString( 'var(--surface-card)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_texture_declaration(): void {
		$r = $this->t->transform( '--texture-card: #fff;' );
		$this->assertStringContainsString( '--surface-card:', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_size_var_m(): void {
		$r = $this->t->transform( 'width: var(--width-m);' );
		$this->assertStringContainsString( 'var(--width-50)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_size_var_xl(): void {
		$r = $this->t->transform( 'height: var(--height-xl);' );
		$this->assertStringContainsString( 'var(--height-80)', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_standalone_h_channel_flagged(): void {
		$r = $this->t->transform( 'filter: hue-rotate(calc(var(--primary-h) * 1deg));' );
		$this->assertStringContainsString( '/* ACSS3: needs manual review', $r['css'] );
		$this->assertSame( 0, $r['converted'] );
		$this->assertSame( 1, $r['flagged'] );
	}

	public function test_standalone_rgb_channel_flagged(): void {
		$r = $this->t->transform( 'color: rgb(var(--primary-r), var(--primary-g), var(--primary-b));' );
		$this->assertSame( 3, $r['flagged'] );
	}

	public function test_hsl_component_not_double_flagged(): void {
		// hsl(var(--primary-h), ...) should be CONVERTED, not flagged
		$r = $this->t->transform( 'color: hsl(var(--primary-h), var(--primary-s)%, var(--primary-l)%);' );
		$this->assertSame( 0, $r['flagged'] );
	}

	public function test_hsl_tuple_with_calc_alpha(): void {
		$r = $this->t->transform( 'color: hsl(var(--primary-hsl) / calc(0.5 + 0.2));' );
		$this->assertStringContainsString( 'color-mix(in oklab, var(--primary)', $r['css'] );
		$this->assertStringNotContainsString( 'calc(calc(', $r['css'] );
		$this->assertSame( 1, $r['converted'] );
	}

	public function test_all_color_tokens_supported(): void {
		foreach ( [ 'primary', 'secondary', 'tertiary', 'accent', 'base', 'neutral', 'info', 'success', 'warning', 'danger' ] as $color ) {
			$r = $this->t->transform( "color: var(--{$color}-hsl);" );
			$this->assertStringContainsString( "var(--{$color})", $r['css'], "Failed for color: {$color}" );
		}
	}
}
