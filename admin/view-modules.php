<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap smgss-app">
	<header class="smgss-header">
		<div>
			<h1><?php esc_html_e( 'SMG Site Suite', 'smg-site-suite' ); ?></h1>
			<p><?php esc_html_e( 'Enable only the tools this site needs.', 'smg-site-suite' ); ?></p>
		</div>
		<div class="smgss-search-wrap">
			<input id="smgss-search" type="search" placeholder="<?php esc_attr_e( 'Search modules…', 'smg-site-suite' ); ?>">
		</div>
	</header>

	<div class="smgss-layout">
		<nav class="smgss-sidebar" aria-label="<?php esc_attr_e( 'Module categories', 'smg-site-suite' ); ?>">
			<button class="smgss-category is-active" data-category="all"><?php esc_html_e( 'All Modules', 'smg-site-suite' ); ?></button>
			<?php foreach ( $categories as $key => $label ) : ?>
				<button class="smgss-category" data-category="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</nav>

		<main class="smgss-main">
			<div class="smgss-toolbar">
				<div class="smgss-status-filters">
					<button class="is-active" data-status="all"><?php esc_html_e( 'All', 'smg-site-suite' ); ?></button>
					<button data-status="active"><?php esc_html_e( 'Active', 'smg-site-suite' ); ?></button>
					<button data-status="inactive"><?php esc_html_e( 'Inactive', 'smg-site-suite' ); ?></button>
				</div>
			</div>

			<section class="smgss-grid" id="smgss-grid">
				<?php foreach ( $modules as $slug => $module ) : $is_active = in_array( $slug, $active, true ); ?>
					<article class="smgss-card" data-module="<?php echo esc_attr( $slug ); ?>" data-category="<?php echo esc_attr( $module['category'] ); ?>" data-status="<?php echo $is_active ? 'active' : 'inactive'; ?>">
						<div class="smgss-card-copy">
							<span class="smgss-card-category"><?php echo esc_html( $categories[ $module['category'] ] ?? $module['category'] ); ?></span>
							<h2><?php echo esc_html( $module['name'] ); ?></h2>
							<p><?php echo esc_html( $module['description'] ); ?></p>
						</div>
						<label class="smgss-switch">
							<input class="smgss-module-toggle" type="checkbox" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $is_active ); ?>>
							<span aria-hidden="true"></span>
							<span class="screen-reader-text"><?php echo esc_html( sprintf( __( 'Toggle %s', 'smg-site-suite' ), $module['name'] ) ); ?></span>
						</label>
					</article>
				<?php endforeach; ?>
			</section>
		</main>
	</div>
</div>
