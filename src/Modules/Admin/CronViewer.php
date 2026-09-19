<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class CronViewer implements ModuleInterface {
    public function register(): void {
        add_action('admin_menu', [$this, 'menu'], 80);
    }

    public function menu(): void {
        add_submenu_page(
            'smg-site-suite',
            __('Cron Viewer', 'smg-site-suite'),
            __('Cron Viewer', 'smg-site-suite'),
            'manage_options',
            'smg-site-suite-cron-viewer',
            [$this, 'render']
        );
    }

    public function events(int $limit = 500): array {
        $limit = max(1, min(500, $limit));
        $cron = _get_cron_array();

        if (!is_array($cron)) {
            return [];
        }

        $items = [];
        foreach ($cron as $timestamp => $hooks) {
            foreach ((array) $hooks as $hook => $events) {
                foreach ((array) $events as $event) {
                    $items[] = [
                        'timestamp' => (int) $timestamp,
                        'next_run' => wp_date(DATE_ATOM, (int) $timestamp),
                        'hook' => (string) $hook,
                        'schedule' => isset($event['schedule']) && $event['schedule'] !== ''
                            ? (string) $event['schedule']
                            : 'one-time',
                        'args' => array_values((array) ($event['args'] ?? [])),
                    ];

                    if (count($items) >= $limit) {
                        return $items;
                    }
                }
            }
        }

        return $items;
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $events = $this->events(500);

        echo '<div class="wrap"><h1>'.esc_html__('WordPress Cron Viewer', 'smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Read-only view of scheduled WordPress cron events.', 'smg-site-suite').'</p>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Next Run', 'smg-site-suite').'</th><th>'.esc_html__('Hook', 'smg-site-suite').'</th><th>'.esc_html__('Schedule', 'smg-site-suite').'</th><th>'.esc_html__('Arguments', 'smg-site-suite').'</th></tr></thead><tbody>';

        foreach ($events as $event) {
            echo '<tr><td>'.esc_html(wp_date('Y-m-d H:i:s', (int) $event['timestamp'])).'</td>';
            echo '<td><code>'.esc_html($event['hook']).'</code></td>';
            echo '<td>'.esc_html($event['schedule'] === 'one-time'
                ? __('One-time', 'smg-site-suite')
                : $event['schedule']).'</td>';
            echo '<td><code>'.esc_html(wp_json_encode($event['args'])).'</code></td></tr>';
        }

        echo '</tbody></table>';
        if (count($events) >= 500) {
            echo '<p class="description">'.esc_html__(
                'Showing the first 500 scheduled events.',
                'smg-site-suite'
            ).'</p>';
        }
        echo '</div>';
    }
}
