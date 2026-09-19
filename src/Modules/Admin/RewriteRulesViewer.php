<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class RewriteRulesViewer implements ModuleInterface {
    public function register(): void {
        add_action('admin_menu', [$this, 'menu'], 88);
    }

    public function menu(): void {
        add_submenu_page(
            'smg-site-suite',
            __('Rewrite Rules', 'smg-site-suite'),
            __('Rewrite Rules', 'smg-site-suite'),
            'manage_options',
            'smg-site-suite-rewrite-rules',
            [$this, 'render']
        );
    }

    public function rules(int $limit = 1000, string $search = ''): array {
        $limit = max(1, min(1000, $limit));
        $search = sanitize_text_field($search);
        $stored = get_option('rewrite_rules', []);

        if (!is_array($stored)) {
            $stored = [];
        }

        $rows = [];
        foreach ($stored as $pattern => $target) {
            $pattern = (string) $pattern;
            $target = (string) $target;

            if (
                $search !== ''
                && stripos($pattern.' '.$target, $search) === false
            ) {
                continue;
            }

            $rows[] = [
                'pattern' => $pattern,
                'target' => $target,
            ];

            if (count($rows) >= $limit) {
                break;
            }
        }

        return [
            'stored_count' => count($stored),
            'count' => count($rows),
            'rules' => $rows,
        ];
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $result = $this->rules(1000);

        echo '<div class="wrap"><h1>'.esc_html__('Rewrite Rules Viewer', 'smg-site-suite').'</h1>';
        echo '<p>'.esc_html(
            sprintf(
                /* translators: %d: Number of stored rewrite rules. */
                __('%d rewrite rules are currently stored.', 'smg-site-suite'),
                $result['stored_count']
            )
        ).'</p>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Pattern', 'smg-site-suite').'</th><th>'.esc_html__('Target', 'smg-site-suite').'</th></tr></thead><tbody>';

        foreach ($result['rules'] as $rule) {
            echo '<tr><td><code>'.esc_html($rule['pattern']).'</code></td>';
            echo '<td><code>'.esc_html($rule['target']).'</code></td></tr>';
        }

        echo '</tbody></table></div>';
    }
}
