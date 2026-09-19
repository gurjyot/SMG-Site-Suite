<?php
namespace SMG\SiteSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	private static ?self $instance = null;

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public function boot(): void {
		add_action( 'plugins_loaded', [ $this, 'load_active_modules' ], 5 );
		if ( is_admin() ) {
			require_once SMG_SITE_SUITE_PATH . 'includes/class-admin.php';
			Admin::instance()->boot();
		}
	}

	public function load_active_modules(): void {
		$available = Module_Registry::all();
		foreach ( Module_Registry::active() as $slug ) {
			if ( empty( $available[ $slug ]['file'] ) ) {
				continue;
			}
			$file = SMG_SITE_SUITE_PATH . $available[ $slug ]['file'];
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	}
}
