<?php
namespace SMG\SiteSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Module_Registry {
	private const OPTION = 'smg_site_suite_active_modules';

	public static function all(): array {
		return [
			'disable-comments' => [
				'name' => __( 'Disable Comments', 'smg-site-suite' ),
				'description' => __( 'Disable WordPress comments and comment-related admin surfaces.', 'smg-site-suite' ),
				'category' => 'content',
				'file' => 'modules/content/disable-comments.php',
			],
			'svg-upload' => [
				'name' => __( 'Safe SVG Upload', 'smg-site-suite' ),
				'description' => __( 'Allow SVG uploads with strict sanitization and capability checks.', 'smg-site-suite' ),
				'category' => 'media',
				'file' => 'modules/media/svg-upload.php',
			],
			'duplicate-content' => [
				'name' => __( 'Duplicate Content', 'smg-site-suite' ),
				'description' => __( 'Duplicate posts, pages, and supported public post types.', 'smg-site-suite' ),
				'category' => 'content',
				'file' => 'modules/content/duplicate-content.php',
			],
		];
	}

	public static function categories(): array {
		return [
			'admin' => __( 'Admin', 'smg-site-suite' ),
			'content' => __( 'Content', 'smg-site-suite' ),
			'media' => __( 'Media', 'smg-site-suite' ),
			'optimization' => __( 'Performance', 'smg-site-suite' ),
			'security' => __( 'Security', 'smg-site-suite' ),
			'utilities' => __( 'Utilities', 'smg-site-suite' ),
			'woocommerce' => __( 'WooCommerce', 'smg-site-suite' ),
		];
	}

	public static function active(): array {
		$value = get_option( self::OPTION, [] );
		return is_array( $value ) ? array_values( array_unique( array_map( 'sanitize_key', $value ) ) ) : [];
	}

	public static function save_active( array $modules ): void {
		$known = array_keys( self::all() );
		$modules = array_values( array_intersect( array_map( 'sanitize_key', $modules ), $known ) );
		update_option( self::OPTION, $modules, false );
	}
}
