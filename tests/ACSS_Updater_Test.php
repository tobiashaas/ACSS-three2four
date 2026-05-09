<?php

use PHPUnit\Framework\TestCase;

final class ACSS_Updater_Test extends TestCase {

	public function test_register_builds_update_checker_for_github_releases(): void {
		$vcs_api = new class() {
			public bool $release_assets_enabled = false;

			public function enableReleaseAssets(): void {
				$this->release_assets_enabled = true;
			}
		};

		$checker = new class( $vcs_api ) {
			private object $vcs_api;

			public function __construct( object $vcs_api ) {
				$this->vcs_api = $vcs_api;
			}

			public function getVcsApi(): object {
				return $this->vcs_api;
			}
		};

		$calls = [];
		$updater = new ACSS_Updater(
			ACSS3TO4_FILE,
			'https://github.com/tobiashaas/ACSS-three2four/',
			'acss3-to-4',
			static function ( string $repo_url, string $plugin_file, string $slug ) use ( &$calls, $checker ) {
				$calls[] = compact( 'repo_url', 'plugin_file', 'slug' );
				return $checker;
			}
		);

		$updater->register();

		$this->assertCount( 1, $calls );
		$this->assertSame( 'https://github.com/tobiashaas/ACSS-three2four/', $calls[0]['repo_url'] );
		$this->assertSame( ACSS3TO4_FILE, $calls[0]['plugin_file'] );
		$this->assertSame( 'acss3-to-4', $calls[0]['slug'] );
		$this->assertTrue( $vcs_api->release_assets_enabled );
	}
}
