<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class SiteInventoryExport implements ModuleInterface {
    public function register(): void {
        add_action('admin_menu', [$this, 'menu'], 89);
        add_action('admin_post_smg_site_suite_export_inventory', [$this, 'export']);
    }

    public function menu(): void {
        add_submenu_page(
            'smg-site-suite',
            __('Site Inventory', 'smg-site-suite'),
            __('Site Inventory', 'smg-site-suite'),
            'manage_options',
            'smg-site-suite-inventory',
            [$this, 'render']
        );
    }

    public function inventory(): array {
        require_once ABSPATH.'wp-admin/includes/plugin.php';

        global $wp_version, $wpdb;

        $theme = wp_get_theme();
        $plugins = get_plugins();
        $active = (array) get_option('active_plugins', []);
        $pluginRows = [];

        foreach ($plugins as $file => $plugin) {
            $pluginRows[] = [
                'file' => (string) $file,
                'name' => (string) ($plugin['Name'] ?? $file),
                'version' => (string) ($plugin['Version'] ?? ''),
                'active' => in_array($file, $active, true),
            ];
        }

        usort(
            $pluginRows,
            static fn(array $left, array $right): int =>
                strcasecmp($left['name'], $right['name'])
        );

        return [
            'schema' => 1,
            'generated_at' => gmdate('c'),
            'site' => [
                'home' => home_url('/'),
                'site' => site_url('/'),
                'multisite' => is_multisite(),
                'timezone' => wp_timezone_string(),
            ],
            'runtime' => [
                'wordpress' => (string) $wp_version,
                'php' => PHP_VERSION,
                'database' => (string) $wpdb->db_version(),
                'memory_limit' => (string) WP_MEMORY_LIMIT,
            ],
            'theme' => [
                'stylesheet' => $theme->get_stylesheet(),
                'name' => (string) $theme->get('Name'),
                'version' => (string) $theme->get('Version'),
            ],
            'plugins' => $pluginRows,
            'site_suite' => [
                'version' => SMG_SITE_SUITE_VERSION,
                'active_modules' => array_values(
                    array_map(
                        'sanitize_key',
                        (array) get_option('smg_site_suite_active_modules', [])
                    )
                ),
            ],
        ];
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $url = wp_nonce_url(
            admin_url('admin-post.php?action=smg_site_suite_export_inventory'),
            'smg_site_suite_export_inventory'
        );

        echo '<div class="wrap"><h1>'.esc_html__('Site Inventory Export', 'smg-site-suite').'</h1>';
        echo '<p>'.esc_html__(
            'Export a JSON inventory of WordPress, PHP, theme, installed plugins, active plugins, and active Site Suite modules. No passwords or plugin settings are included.',
            'smg-site-suite'
        ).'</p>';
        echo '<p><a class="button button-primary" href="'.esc_url($url).'">';
        echo esc_html__('Download Inventory JSON', 'smg-site-suite').'</a></p></div>';
    }

    public function export(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions.', 'smg-site-suite'));
        }

        check_admin_referer('smg_site_suite_export_inventory');

        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename="smg-site-inventory-'.gmdate('Y-m-d').'.json"'
        );

        echo wp_json_encode(
            $this->inventory(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
        exit;
    }
}
