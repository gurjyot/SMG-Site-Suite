<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;
use WP_Error;

final class RedirectManager implements SettingsModuleInterface {
    private const OPTION = 'smg_site_suite_redirects';
    private const STATS = 'smg_site_suite_redirect_stats';

    public function register(): void {
        add_action('template_redirect', [$this, 'redirect'], 0);
        add_action('admin_menu', [$this, 'menu'], 45);
    }

    public function menu(): void {
        add_submenu_page(
            'smg-site-suite',
            __('Redirect Stats', 'smg-site-suite'),
            __('Redirect Stats', 'smg-site-suite'),
            'manage_options',
            'smg-site-suite-redirect-stats',
            [$this, 'renderStats']
        );
    }

    public function settingsSchema(): array {
        return [[
            'key' => 'rules',
            'type' => 'textarea',
            'label' => __('Redirect rules', 'smg-site-suite'),
            'description' => __(
                "One per line: /old-path => /new-path | 301\nSupported codes: 301, 302, 307, 308. Only local paths are allowed.",
                'smg-site-suite'
            ),
        ]];
    }

    public function settings(): array {
        $value = get_option(self::OPTION, []);
        return is_array($value) ? $value : [];
    }

    public function saveSettings(array $input): void {
        update_option(
            self::OPTION,
            ['rules' => sanitize_textarea_field((string) ($input['rules'] ?? ''))],
            false
        );
    }

    public function redirect(): void {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        $requestUri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
            : '/';
        $path = (string) wp_parse_url(
            home_url(add_query_arg([], $requestUri)),
            PHP_URL_PATH
        );

        foreach ($this->rules() as $from => $rule) {
            if (untrailingslashit($path) !== untrailingslashit($from)) {
                continue;
            }

            $this->recordHit($from);
            wp_safe_redirect(home_url((string) $rule['to']), (int) $rule['code']);
            exit;
        }
    }

    public function rules(): array {
        $out = [];
        $raw = (string) ($this->settings()['rules'] ?? '');

        foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
            if (!str_contains($line, '=>')) {
                continue;
            }

            [$from, $target] = array_map('trim', explode('=>', $line, 2));
            $parts = array_map('trim', explode('|', $target, 2));
            $to = $parts[0] ?? '';
            $code = isset($parts[1]) ? absint($parts[1]) : 301;

            if (!in_array($code, [301, 302, 307, 308], true)) {
                $code = 301;
            }

            if (!$this->validLocalPath($from) || !$this->validLocalPath($to)) {
                continue;
            }

            $out[$from] = [
                'to' => $to,
                'code' => $code,
            ];
        }

        return $out;
    }

    public function rulesWithStats(): array {
        $stats = get_option(self::STATS, []);
        if (!is_array($stats)) {
            $stats = [];
        }

        $rows = [];
        foreach ($this->rules() as $from => $rule) {
            $stat = $stats[$from] ?? ['hits' => 0, 'last' => 0];
            $last = max(0, (int) ($stat['last'] ?? 0));

            $rows[] = [
                'from' => $from,
                'to' => (string) $rule['to'],
                'code' => (int) $rule['code'],
                'hits' => max(0, (int) ($stat['hits'] ?? 0)),
                'last_used' => $last,
                'last_used_iso' => $last > 0 ? wp_date(DATE_ATOM, $last) : '',
            ];
        }

        return $rows;
    }

    public function upsertRule(string $from, string $to, int $code = 301) {
        $from = $this->normalizePath($from);
        $to = $this->normalizePath($to);

        if (!$this->validLocalPath($from) || !$this->validLocalPath($to)) {
            return new WP_Error(
                'smg_site_suite_invalid_redirect_path',
                __('Redirect paths must be local paths beginning with /.', 'smg-site-suite')
            );
        }

        if (!in_array($code, [301, 302, 307, 308], true)) {
            return new WP_Error(
                'smg_site_suite_invalid_redirect_code',
                __('Redirect code must be 301, 302, 307, or 308.', 'smg-site-suite')
            );
        }

        if (untrailingslashit($from) === untrailingslashit($to)) {
            return new WP_Error(
                'smg_site_suite_redirect_loop',
                __('Redirect source and destination must be different.', 'smg-site-suite')
            );
        }

        $rules = $this->rules();
        $rules[$from] = [
            'to' => $to,
            'code' => $code,
        ];
        $this->storeRules($rules);

        return [
            'from' => $from,
            'to' => $to,
            'code' => $code,
        ];
    }

    public function deleteRule(string $from) {
        $from = $this->normalizePath($from);

        if (!$this->validLocalPath($from)) {
            return new WP_Error(
                'smg_site_suite_invalid_redirect_path',
                __('Redirect path must be a local path beginning with /.', 'smg-site-suite')
            );
        }

        $rules = $this->rules();
        if (!isset($rules[$from])) {
            return new WP_Error(
                'smg_site_suite_redirect_not_found',
                __('Redirect rule was not found.', 'smg-site-suite')
            );
        }

        unset($rules[$from]);
        $this->storeRules($rules);

        $stats = get_option(self::STATS, []);
        if (is_array($stats) && isset($stats[$from])) {
            unset($stats[$from]);
            update_option(self::STATS, $stats, false);
        }

        return true;
    }

    public function renderStats(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap"><h1>'.esc_html__('Redirect Statistics', 'smg-site-suite').'</h1>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('From', 'smg-site-suite').'</th><th>'.esc_html__('To', 'smg-site-suite').'</th><th>'.esc_html__('Code', 'smg-site-suite').'</th><th>'.esc_html__('Hits', 'smg-site-suite').'</th><th>'.esc_html__('Last Used', 'smg-site-suite').'</th></tr></thead><tbody>';

        foreach ($this->rulesWithStats() as $row) {
            echo '<tr><td><code>'.esc_html($row['from']).'</code></td>';
            echo '<td><code>'.esc_html($row['to']).'</code></td>';
            echo '<td>'.esc_html((string) $row['code']).'</td>';
            echo '<td>'.esc_html((string) $row['hits']).'</td>';
            echo '<td>'.esc_html($row['last_used'] > 0
                ? wp_date('Y-m-d H:i', $row['last_used'])
                : '—').'</td></tr>';
        }

        echo '</tbody></table></div>';
    }

    private function storeRules(array $rules): void {
        $lines = [];

        foreach ($rules as $from => $rule) {
            if (!$this->validLocalPath((string) $from)) {
                continue;
            }

            $to = (string) ($rule['to'] ?? '');
            $code = (int) ($rule['code'] ?? 301);

            if (!$this->validLocalPath($to) || !in_array($code, [301, 302, 307, 308], true)) {
                continue;
            }

            $lines[] = $from.' => '.$to.' | '.$code;
        }

        $this->saveSettings(['rules' => implode("\n", $lines)]);
    }

    private function normalizePath(string $path): string {
        $path = sanitize_text_field($path);
        $parsed = wp_parse_url($path);

        if (!is_array($parsed) || isset($parsed['scheme']) || isset($parsed['host'])) {
            return '';
        }

        return (string) ($parsed['path'] ?? '');
    }

    private function validLocalPath(string $path): bool {
        return $path !== ''
            && str_starts_with($path, '/')
            && !str_starts_with($path, '//');
    }

    private function recordHit(string $from): void {
        $stats = get_option(self::STATS, []);
        if (!is_array($stats)) {
            $stats = [];
        }

        $row = $stats[$from] ?? ['hits' => 0, 'last' => 0];
        $row['hits'] = (int) ($row['hits'] ?? 0) + 1;
        $row['last'] = time();
        $stats[$from] = $row;

        if (count($stats) > 500) {
            $stats = array_slice($stats, -500, null, true);
        }

        update_option(self::STATS, $stats, false);
    }
}
