<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class SystemSummary implements ModuleInterface {
    public function register():void{add_action('admin_menu',[$this,'menu'],30);}
    public function menu():void{
        add_submenu_page('smg-site-suite',__('System Summary','smg-site-suite'),__('System Summary','smg-site-suite'),'manage_options','smg-site-suite-system-summary',[$this,'render']);
    }
    public function render():void{
        if(!current_user_can('manage_options'))return;
        global $wpdb,$wp_version;
        $theme=wp_get_theme();
        $data=[
            __('WordPress','smg-site-suite')=>$wp_version,
            __('PHP','smg-site-suite')=>PHP_VERSION,
            __('Database','smg-site-suite')=>$wpdb->db_version(),
            __('Theme','smg-site-suite')=>$theme->get('Name').' '.$theme->get('Version'),
            __('Site URL','smg-site-suite')=>site_url(),
            __('Home URL','smg-site-suite')=>home_url(),
            __('Timezone','smg-site-suite')=>wp_timezone_string()?:'UTC',
            __('Memory limit','smg-site-suite')=>WP_MEMORY_LIMIT,
            __('Multisite','smg-site-suite')=>is_multisite()?__('Yes','smg-site-suite'):__('No','smg-site-suite'),
            __('Debug','smg-site-suite')=>(defined('WP_DEBUG')&&WP_DEBUG)?__('On','smg-site-suite'):__('Off','smg-site-suite'),
        ];
        echo '<div class="wrap"><h1>'.esc_html__('System Summary','smg-site-suite').'</h1><table class="widefat striped" style="max-width:900px"><tbody>';
        foreach($data as $label=>$value)echo '<tr><th style="width:220px">'.esc_html($label).'</th><td><code>'.esc_html((string)$value).'</code></td></tr>';
        echo '</tbody></table></div>';
    }
}
