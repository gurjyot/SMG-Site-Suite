<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DatabaseTableSizes implements ModuleInterface {
    public function register(): void {
        add_action('admin_menu', [$this, 'menu'], 81);
    }

    public function menu(): void {
        add_submenu_page(
            'smg-site-suite',
            __('Database Sizes', 'smg-site-suite'),
            __('Database Sizes', 'smg-site-suite'),
            'manage_options',
            'smg-site-suite-database-sizes',
            [$this, 'render']
        );
    }

    public function tables(int $limit = 200): array {
        global $wpdb;

        $limit = max(1, min(500, $limit));
        $tables = $wpdb->get_results('SHOW TABLE STATUS', ARRAY_A);

        if (!is_array($tables)) {
            return [
                'count' => 0,
                'total_bytes' => 0,
                'tables' => [],
            ];
        }

        usort(
            $tables,
            static function (array $left, array $right): int {
                $leftSize = (int) ($left['Data_length'] ?? 0)
                    + (int) ($left['Index_length'] ?? 0);
                $rightSize = (int) ($right['Data_length'] ?? 0)
                    + (int) ($right['Index_length'] ?? 0);
                return $rightSize <=> $leftSize;
            }
        );

        $rows = [];
        $total = 0;

        foreach ($tables as $table) {
            $data = max(0, (int) ($table['Data_length'] ?? 0));
            $indexes = max(0, (int) ($table['Index_length'] ?? 0));
            $size = $data + $indexes;
            $total += $size;

            if (count($rows) >= $limit) {
                continue;
            }

            $rows[] = [
                'name' => (string) ($table['Name'] ?? ''),
                'rows' => max(0, (int) ($table['Rows'] ?? 0)),
                'data_bytes' => $data,
                'index_bytes' => $indexes,
                'total_bytes' => $size,
            ];
        }

        return [
            'count' => count($rows),
            'total_bytes' => $total,
            'tables' => $rows,
        ];
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $result = $this->tables(500);

        echo '<div class="wrap"><h1>'.esc_html__('Database Table Sizes', 'smg-site-suite').'</h1>';
        echo '<p>'.esc_html__(
            'Read-only size overview. No optimization or deletion is performed.',
            'smg-site-suite'
        ).'</p>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Table', 'smg-site-suite').'</th><th>'.esc_html__('Rows', 'smg-site-suite').'</th><th>'.esc_html__('Data', 'smg-site-suite').'</th><th>'.esc_html__('Indexes', 'smg-site-suite').'</th><th>'.esc_html__('Total', 'smg-site-suite').'</th></tr></thead><tbody>';

        foreach ($result['tables'] as $table) {
            echo '<tr><td><code>'.esc_html($table['name']).'</code></td>';
            echo '<td>'.esc_html(number_format_i18n($table['rows'])).'</td>';
            echo '<td>'.esc_html(size_format($table['data_bytes'], 2)).'</td>';
            echo '<td>'.esc_html(size_format($table['index_bytes'], 2)).'</td>';
            echo '<td><strong>'.esc_html(size_format($table['total_bytes'], 2)).'</strong></td></tr>';
        }

        echo '</tbody></table><p><strong>'.esc_html__('Approximate total:', 'smg-site-suite').' ';
        echo esc_html(size_format($result['total_bytes'], 2)).'</strong></p></div>';
    }
}
