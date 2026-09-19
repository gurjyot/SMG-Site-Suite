<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class CronViewer implements ModuleInterface {
    public function register():void{add_action('admin_menu',[$this,'menu'],80);}

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Cron Viewer','smg-site-suite'),__('Cron Viewer','smg-site-suite'),'manage_options','smg-site-suite-cron-viewer',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        $cron=_get_cron_array();if(!is_array($cron))$cron=[];

        echo '<div class="wrap"><h1>'.esc_html__('WordPress Cron Viewer','smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Read-only view of scheduled WordPress cron events.','smg-site-suite').'</p>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Next Run','smg-site-suite').'</th><th>'.esc_html__('Hook','smg-site-suite').'</th><th>'.esc_html__('Schedule','smg-site-suite').'</th><th>'.esc_html__('Arguments','smg-site-suite').'</th></tr></thead><tbody>';

        $shown=0;
        foreach($cron as $timestamp=>$hooks){
            foreach((array)$hooks as $hook=>$events){
                foreach((array)$events as $event){
                    echo '<tr><td>'.esc_html(wp_date('Y-m-d H:i:s',(int)$timestamp)).'</td><td><code>'.esc_html((string)$hook).'</code></td><td>'.esc_html((string)($event['schedule']??__('One-time','smg-site-suite'))).'</td><td><code>'.esc_html(wp_json_encode($event['args']??[])).'</code></td></tr>';
                    $shown++;if($shown>=500)break 3;
                }
            }
        }

        echo '</tbody></table>';
        if($shown>=500)echo '<p class="description">'.esc_html__('Showing the first 500 scheduled events.','smg-site-suite').'</p>';
        echo '</div>';
    }
}
