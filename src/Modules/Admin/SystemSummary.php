<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class SystemSummary implements ModuleInterface {
    public function register(): void {
        add_action('admin_menu', [$this, 'menu'], 30);
    }

    public function menu(): void {
        add_submenu_page(
            'smg-site-suite',
            __('System Summary', 'smg-site-suite'),
            __('System Summary', 'smg-site-suite'),
            'manage_options',
            'smg-site-suite-system-summary',
            [$this, 'render']
        );
    }

    public function summary(): array {
        global $wpdb, $wp_version;

        $theme = wp_get_theme();

        return [
            'wordpress_version' => (string) $wp_version,
            'php_version' => PHP_VERSION,
            'database_version' => (string) $wpdb->db_version(),
            'theme_name' => (string) $theme->get('Name'),
            'theme_version' => (string) $theme->get('Version'),
            'site_url' => site_url(),
            'home_url' => home_url(),
            'timezone' => wp_timezone_string() ?: 'UTC',
            'memory_limit' => (string) WP_MEMORY_LIMIT,
            'multisite' => is_multisite(),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
        ];
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $summary = $this->summary();
        $rows = [
            __('WordPress', 'smg-site-suite') => $summary['wordpress_version'],
            __('PHP', 'smg-site-suite') => $summary['php_version'],
            __('Database', 'smg-site-suite') => $summary['database_version'],
            __('Theme', 'smg-site-suite') => trim(
                $summary['theme_name'].' '.$summary['theme_version']
            ),
            __('Site URL', 'smg-site-suite') => $summary['site_url'],
            __('Home URL', 'smg-site-suite') => $summary['home_url'],
            __('Timezone', 'smg-site-suite') => $summary['timezone'],
            __('Memory limit', 'smg-site-suite') => $summary['memory_limit'],
            __('Multisite', 'smg-site-suite') => $summary['multisite']
                ? __('Yes', 'smg-site-suite')
                : __('No', 'smg-site-suite'),
            __('Debug', 'smg-site-suite') => $summary['debug']
                ? __('On', 'smg-site-suite')
                : __('Off', 'smg-site-suite'),
        ];

        echo '<div class="wrap"><h1>'.esc_html__('System Summary', 'smg-site-suite').'</h1>';
        echo '<table class="widefat striped" style="max-width:900px"><tbody>';

        foreach ($rows as $label => $value) {
            echo '<tr><th style="width:220px">'.esc_html($label).'</th>';
            echo '<td><code>'.esc_html((string) $value).'</code></td></tr>';
        }

        echo '</tbody></table></div>';
    }
}
