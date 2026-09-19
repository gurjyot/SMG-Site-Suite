<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class RewriteRulesViewer implements ModuleInterface {
    public function register():void{add_action('admin_menu',[$this,'menu'],88);}

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Rewrite Rules','smg-site-suite'),__('Rewrite Rules','smg-site-suite'),'manage_options','smg-site-suite-rewrite-rules',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        $rules=get_option('rewrite_rules',[]);if(!is_array($rules))$rules=[];
        echo '<div class="wrap"><h1>'.esc_html__('Rewrite Rules Viewer','smg-site-suite').'</h1>';
        echo '<p>'.esc_html(sprintf(__('%d rewrite rules are currently stored.','smg-site-suite'),count($rules))).'</p>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Pattern','smg-site-suite').'</th><th>'.esc_html__('Target','smg-site-suite').'</th></tr></thead><tbody>';
        foreach(array_slice($rules,0,1000,true) as $pattern=>$target)echo '<tr><td><code>'.esc_html((string)$pattern).'</code></td><td><code>'.esc_html((string)$target).'</code></td></tr>';
        echo '</tbody></table></div>';
    }
}
