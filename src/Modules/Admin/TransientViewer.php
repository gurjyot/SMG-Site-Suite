<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class TransientViewer implements ModuleInterface {
    public function register():void{add_action('admin_menu',[$this,'menu'],87);}

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Transient Viewer','smg-site-suite'),__('Transient Viewer','smg-site-suite'),'manage_options','smg-site-suite-transients',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        global $wpdb;
        $prefix=$wpdb->esc_like('_transient_').'%';
        $timeoutPrefix='_transient_timeout_';
        $rows=$wpdb->get_results($wpdb->prepare("SELECT option_name, LENGTH(option_value) AS bytes FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE %s ORDER BY bytes DESC LIMIT 500",$prefix,$wpdb->esc_like($timeoutPrefix).'%'),ARRAY_A);
        if(!is_array($rows))$rows=[];

        echo '<div class="wrap"><h1>'.esc_html__('Transient Viewer','smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Read-only overview of up to 500 stored WordPress transients. Persistent object-cache-only transients may not appear here.','smg-site-suite').'</p>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Transient','smg-site-suite').'</th><th>'.esc_html__('Size','smg-site-suite').'</th><th>'.esc_html__('Expires','smg-site-suite').'</th></tr></thead><tbody>';
        foreach($rows as $row){
            $name=(string)$row['option_name'];
            $key=substr($name,strlen('_transient_'));
            $timeout=(int)get_option('_transient_timeout_'.$key,0);
            $expires=$timeout>0?wp_date('Y-m-d H:i',$timeout):__('No timeout','smg-site-suite');
            echo '<tr><td><code>'.esc_html($key).'</code></td><td>'.esc_html(size_format((int)$row['bytes'],2)).'</td><td>'.esc_html($expires).'</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
