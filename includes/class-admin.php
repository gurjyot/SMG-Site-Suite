<?php
namespace SMG\SiteSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admin {
	private static ?self $instance = null;

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public function boot(): void {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
		add_action( 'wp_ajax_smg_site_suite_toggle_module', [ $this, 'toggle_module' ] );
	}

	public function menu(): void {
		add_menu_page(
			__( 'SMG Site Suite', 'smg-site-suite' ),
			__( 'Site Suite', 'smg-site-suite' ),
			'manage_options',
			'smg-site-suite',
			[ $this, 'render' ],
			'dashicons-screenoptions',
			58
		);
	}

	public function assets( string $hook ): void {
		if ( 'toplevel_page_smg-site-suite' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'smg-site-suite-admin', SMG_SITE_SUITE_URL . 'admin/assets/admin.css', [], SMG_SITE_SUITE_VERSION );
		wp_enqueue_script( 'smg-site-suite-admin', SMG_SITE_SUITE_URL . 'admin/assets/admin.js', [], SMG_SITE_SUITE_VERSION, true );
		wp_localize_script( 'smg-site-suite-admin', 'smgSiteSuite', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'smg_site_suite_modules' ),
		] );
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$modules = Module_Registry::all();
		$active = Module_Registry::active();
		$categories = Module_Registry::categories();
		include SMG_SITE_SUITE_PATH . 'admin/view-modules.php';
	}

	public function toggle_module(): void {
		check_ajax_referer( 'smg_site_suite_modules', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'smg-site-suite' ) ], 403 );
		}
		$slug = isset( $_POST['module'] ) ? sanitize_key( wp_unslash( $_POST['module'] ) ) : '';
		$enabled = isset( $_POST['enabled'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['enabled'] ) );
		$known = Module_Registry::all();
		if ( ! isset( $known[ $slug ] ) ) {
			wp_send_json_error( [ 'message' => __( 'Unknown module.', 'smg-site-suite' ) ], 400 );
		}
		$active = Module_Registry::active();
		if ( $enabled ) {
			$active[] = $slug;
		} else {
			$active = array_values( array_diff( $active, [ $slug ] ) );
		}
		Module_Registry::save_active( $active );
		wp_send_json_success( [ 'active' => Module_Registry::active() ] );
	}
}
