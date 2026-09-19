<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DatabaseTableSizes implements ModuleInterface {
    public function register():void{add_action('admin_menu',[$this,'menu'],81);}

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Database Sizes','smg-site-suite'),__('Database Sizes','smg-site-suite'),'manage_options','smg-site-suite-database-sizes',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        global $wpdb;

        $tables=$wpdb->get_results('SHOW TABLE STATUS',ARRAY_A);
        if(!is_array($tables))$tables=[];

        usort($tables,static fn(array $a,array $b):int=>((int)($b['Data_length']??0)+(int)($b['Index_length']??0))<=>((int)($a['Data_length']??0)+(int)($a['Index_length']??0)));

        $total=0;
        echo '<div class="wrap"><h1>'.esc_html__('Database Table Sizes','smg-site-suite').'</h1><p>'.esc_html__('Read-only size overview. No optimization or deletion is performed.','smg-site-suite').'</p>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Table','smg-site-suite').'</th><th>'.esc_html__('Rows','smg-site-suite').'</th><th>'.esc_html__('Data','smg-site-suite').'</th><th>'.esc_html__('Indexes','smg-site-suite').'</th><th>'.esc_html__('Total','smg-site-suite').'</th></tr></thead><tbody>';

        foreach($tables as $table){
            $data=(int)($table['Data_length']??0);$index=(int)($table['Index_length']??0);$size=$data+$index;$total+=$size;
            echo '<tr><td><code>'.esc_html((string)($table['Name']??'')).'</code></td><td>'.esc_html(number_format_i18n((int)($table['Rows']??0))).'</td><td>'.esc_html(size_format($data,2)).'</td><td>'.esc_html(size_format($index,2)).'</td><td><strong>'.esc_html(size_format($size,2)).'</strong></td></tr>';
        }

        echo '</tbody></table><p><strong>'.esc_html__('Approximate total:','smg-site-suite').' '.esc_html(size_format($total,2)).'</strong></p></div>';
    }
}
