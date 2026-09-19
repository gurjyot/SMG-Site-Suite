<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class NotFoundTracker implements SettingsModuleInterface {
    private const OPTION = 'smg_site_suite_404_log';
    private const SETTINGS = 'smg_site_suite_404_settings';

    public function register(): void {
        add_action('template_redirect', [$this, 'track'], 99);
        add_action('admin_menu', [$this, 'menu'], 40);
        add_action('admin_post_smg_site_suite_404_redirect', [$this, 'createRedirect']);
    }

    public function settingsSchema(): array {
        return [[
            'key' => 'limit',
            'type' => 'number',
            'label' => __('Maximum stored 404 entries', 'smg-site-suite'),
            'default' => 200,
        ]];
    }

    public function settings(): array {
        $value = get_option(self::SETTINGS, ['limit' => 200]);
        return is_array($value) ? $value : [];
    }

    public function saveSettings(array $input): void {
        update_option(
            self::SETTINGS,
            ['limit' => max(20, min(2000, absint($input['limit'] ?? 200)))],
            false
        );
    }

    public function entries(int $limit = 200): array {
        $limit = max(1, min(2000, $limit));
        $log = get_option(self::OPTION, []);

        if (!is_array($log)) {
            return [];
        }

        uasort(
            $log,
            static fn(array $left, array $right): int =>
                (int) ($right['last'] ?? 0) <=> (int) ($left['last'] ?? 0)
        );

        $entries = [];
        foreach ($log as $row) {
            $url = esc_url_raw((string) ($row['url'] ?? ''));
            $entries[] = [
                'url' => $url,
                'path' => (string) wp_parse_url($url, PHP_URL_PATH),
                'hits' => max(0, (int) ($row['count'] ?? 0)),
                'last_seen' => max(0, (int) ($row['last'] ?? 0)),
                'last_seen_iso' => !empty($row['last'])
                    ? wp_date(DATE_ATOM, (int) $row['last'])
                    : '',
                'referrer' => esc_url_raw((string) ($row['referer'] ?? '')),
            ];

            if (count($entries) >= $limit) {
                break;
            }
        }

        return $entries;
    }

    public function track(): void {
        if (!is_404() || is_admin() || wp_doing_ajax()) {
            return;
        }

        $requestUri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
            : '/';
        $url = esc_url_raw(home_url($requestUri));
        $referer = $this->requestReferer();
        $log = get_option(self::OPTION, []);

        if (!is_array($log)) {
            $log = [];
        }

        $key = md5($url);
        $row = $log[$key] ?? [
            'url' => $url,
            'count' => 0,
            'last' => 0,
            'referer' => '',
        ];

        $row['count'] = (int) $row['count'] + 1;
        $row['last'] = time();
        if ($referer !== '') {
            $row['referer'] = $referer;
        }

        $log[$key] = $row;
        uasort(
            $log,
            static fn(array $left, array $right): int =>
                (int) $right['last'] <=> (int) $left['last']
        );

        $limit = (int) ($this->settings()['limit'] ?? 200);
        if (count($log) > $limit) {
            $log = array_slice($log, 0, $limit, true);
        }

        update_option(self::OPTION, $log, false);
    }

    private function requestReferer(): string {
        if (!isset($_SERVER['HTTP_REFERER']) || !is_string($_SERVER['HTTP_REFERER'])) {
            return '';
        }

        return esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER']));
    }

    public function createRedirect(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions.', 'smg-site-suite'));
        }

        $from = isset($_POST['from'])
            ? sanitize_text_field(wp_unslash($_POST['from']))
            : '';
        $to = isset($_POST['to'])
            ? sanitize_text_field(wp_unslash($_POST['to']))
            : '';

        if (!str_starts_with($from, '/') || !str_starts_with($to, '/')) {
            wp_die(esc_html__('Redirect paths must begin with /.', 'smg-site-suite'));
        }

        check_admin_referer('smg_site_suite_404_redirect_'.md5($from));

        $option = get_option('smg_site_suite_redirects', []);
        if (!is_array($option)) {
            $option = [];
        }

        $rules = (string) ($option['rules'] ?? '');
        $line = $from.' => '.$to;
        $lines = array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $rules) ?: [])
        );
        $replaced = false;

        foreach ($lines as $index => $existing) {
            if (str_starts_with($existing, $from.' =>')) {
                $lines[$index] = $line;
                $replaced = true;
                break;
            }
        }

        if (!$replaced) {
            $lines[] = $line;
        }

        update_option(
            'smg_site_suite_redirects',
            ['rules' => implode("\n", $lines)],
            false
        );

        wp_safe_redirect(
            add_query_arg(
                ['page' => 'smg-site-suite-404-log', 'redirect_added' => '1'],
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function menu(): void {
        add_submenu_page(
            'smg-site-suite',
            __('404 Log', 'smg-site-suite'),
            __('404 Log', 'smg-site-suite'),
            'manage_options',
            'smg-site-suite-404-log',
            [$this, 'render']
        );
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap"><h1>'.esc_html__('404 Log', 'smg-site-suite').'</h1>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('URL', 'smg-site-suite').'</th><th>'.esc_html__('Hits', 'smg-site-suite').'</th><th>'.esc_html__('Last seen', 'smg-site-suite').'</th><th>'.esc_html__('Referrer', 'smg-site-suite').'</th><th>'.esc_html__('Create Redirect', 'smg-site-suite').'</th></tr></thead><tbody>';

        foreach ($this->entries(2000) as $row) {
            echo '<tr><td><code>'.esc_html($row['url']).'</code></td>';
            echo '<td>'.esc_html((string) $row['hits']).'</td>';
            echo '<td>'.esc_html($row['last_seen'] > 0
                ? wp_date('Y-m-d H:i', $row['last_seen'])
                : '—').'</td>';
            echo '<td>'.esc_html($row['referrer']).'</td><td>';
            echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" style="display:flex;gap:6px;min-width:260px">';
            echo '<input type="hidden" name="action" value="smg_site_suite_404_redirect">';
            echo '<input type="hidden" name="from" value="'.esc_attr($row['path']).'">';
            wp_nonce_field('smg_site_suite_404_redirect_'.md5($row['path']));
            echo '<input type="text" name="to" placeholder="/new-path" required style="width:150px">';
            echo '<button class="button button-small">'.esc_html__('Add', 'smg-site-suite').'</button>';
            echo '</form></td></tr>';
        }

        echo '</tbody></table></div>';
    }
}
