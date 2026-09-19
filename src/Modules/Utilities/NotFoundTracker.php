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
        $limit = max(20, min(2000, absint($input['limit'] ?? 200)));
        update_option(self::SETTINGS, ['limit' => $limit], false);
    }

    public function track(): void {
        if (!is_404() || is_admin() || wp_doing_ajax()) {
            return;
        }

        $url = esc_url_raw(
            home_url(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'))
        );
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

        $row['count'] = (int)$row['count'] + 1;
        $row['last'] = time();

        if ($referer !== '') {
            $row['referer'] = $referer;
        }

        $log[$key] = $row;

        uasort(
            $log,
            static fn(array $a, array $b): int => (int)$b['last'] <=> (int)$a['last']
        );

        $limit = (int)($this->settings()['limit'] ?? 200);
        if (count($log) > $limit) {
            $log = array_slice($log, 0, $limit, true);
        }

        update_option(self::OPTION, $log, false);
    }

    private function requestReferer(): string {
        $value = $_SERVER['HTTP_REFERER'] ?? '';
        if (!is_string($value) || $value === '') {
            return '';
        }

        return esc_url_raw(wp_unslash($value));
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

        $rules = (string)($option['rules'] ?? '');
        $lines = array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $rules) ?: [])
        );
        $newRule = $from.' => '.$to;
        $replaced = false;

        foreach ($lines as $index => $existing) {
            if (!str_starts_with($existing, $from.' =>')) {
                continue;
            }

            $lines[$index] = $newRule;
            $replaced = true;
            break;
        }

        if (!$replaced) {
            $lines[] = $newRule;
        }

        update_option(
            'smg_site_suite_redirects',
            ['rules' => implode("\n", $lines)],
            false
        );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => 'smg-site-suite-404-log',
                    'redirect_added' => '1',
                ],
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

        $log = get_option(self::OPTION, []);
        if (!is_array($log)) {
            $log = [];
        }

        $headings = [
            __('URL', 'smg-site-suite'),
            __('Hits', 'smg-site-suite'),
            __('Last seen', 'smg-site-suite'),
            __('Referrer', 'smg-site-suite'),
            __('Create Redirect', 'smg-site-suite'),
        ];

        echo '<div class="wrap">';
        echo '<h1>'.esc_html__('404 Log', 'smg-site-suite').'</h1>';
        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        foreach ($headings as $heading) {
            echo '<th>'.esc_html($heading).'</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ($log as $row) {
            $this->renderRow($row);
        }

        echo '</tbody></table>';
        echo '</div>';
    }

    private function renderRow(array $row): void {
        $url = (string)($row['url'] ?? '');
        $path = (string)wp_parse_url($url, PHP_URL_PATH);

        echo '<tr>';
        echo '<td><code>'.esc_html($url).'</code></td>';
        echo '<td>'.esc_html((string)($row['count'] ?? 0)).'</td>';
        echo '<td>'.esc_html(wp_date('Y-m-d H:i', (int)($row['last'] ?? 0))).'</td>';
        echo '<td>'.esc_html((string)($row['referer'] ?? '')).'</td>';
        echo '<td>';

        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"'
            .' style="display:flex;gap:6px;min-width:260px">';
        echo '<input type="hidden" name="action" value="smg_site_suite_404_redirect">';
        echo '<input type="hidden" name="from" value="'.esc_attr($path).'">';

        wp_nonce_field('smg_site_suite_404_redirect_'.md5($path));

        echo '<input type="text" name="to" placeholder="/new-path" required style="width:150px">';
        echo '<button class="button button-small">'.esc_html__('Add', 'smg-site-suite').'</button>';
        echo '</form>';

        echo '</td>';
        echo '</tr>';
    }
}
