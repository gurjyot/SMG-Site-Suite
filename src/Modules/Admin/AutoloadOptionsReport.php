<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class AutoloadOptionsReport implements ModuleInterface {
    public function register():void{add_action('admin_menu',[$this,'menu'],86);}

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Autoload Options','smg-site-suite'),__('Autoload Options','smg-site-suite'),'manage_options','smg-site-suite-autoload-options',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        global $wpdb;
        $rows=$wpdb->get_results("SELECT option_name, LENGTH(option_value) AS bytes, autoload FROM {$wpdb->options} WHERE autoload IN ('yes','on','auto-on','auto') ORDER BY bytes DESC LIMIT 500",ARRAY_A);
        if(!is_array($rows))$rows=[];
        $total=0;
        foreach($rows as $row)$total+=(int)($row['bytes']??0);

        echo '<div class="wrap"><h1>'.esc_html__('Autoloaded Options Report','smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Read-only view of the largest autoloaded WordPress options. Showing up to 500 rows.','smg-site-suite').'</p>';
        echo '<p><strong>'.esc_html__('Displayed size:','smg-site-suite').' '.esc_html(size_format($total,2)).'</strong></p>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Option','smg-site-suite').'</th><th>'.esc_html__('Size','smg-site-suite').'</th><th>'.esc_html__('Autoload','smg-site-suite').'</th></tr></thead><tbody>';
        foreach($rows as $row)echo '<tr><td><code>'.esc_html((string)$row['option_name']).'</code></td><td>'.esc_html(size_format((int)$row['bytes'],2)).'</td><td>'.esc_html((string)$row['autoload']).'</td></tr>';
        echo '</tbody></table></div>';
    }
}
