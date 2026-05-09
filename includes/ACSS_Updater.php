<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACSS_Updater {

	private string $plugin_file;
	private string $repo_url;
	private string $slug;

	/** @var callable|null */
	private $factory;

	public function __construct(
		string $plugin_file,
		string $repo_url = 'https://github.com/tobiashaas/ACSS-three2four/',
		string $slug = 'acss3-to-4',
		?callable $factory = null
	) {
		$this->plugin_file = $plugin_file;
		$this->repo_url    = $repo_url;
		$this->slug        = $slug;
		$this->factory     = $factory;
	}

	public function register(): void {
		$factory = $this->factory;

		if ( null === $factory ) {
			if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
				return;
			}

			$factory = [ 'YahnisElsts\PluginUpdateChecker\v5\PucFactory', 'buildUpdateChecker' ];
		}

		$update_checker = $factory( $this->repo_url, $this->plugin_file, $this->slug );

		if ( is_object( $update_checker ) && method_exists( $update_checker, 'getVcsApi' ) ) {
			$vcs_api = $update_checker->getVcsApi();

			if ( is_object( $vcs_api ) && method_exists( $vcs_api, 'enableReleaseAssets' ) ) {
				$vcs_api->enableReleaseAssets();
			}
		}
	}
}
