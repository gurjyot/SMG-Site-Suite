<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DebugLogViewer implements ModuleInterface {
    public function register():void{
        add_action('admin_menu',[$this,'menu'],85);
    }

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Debug Log','smg-site-suite'),__('Debug Log','smg-site-suite'),'manage_options','smg-site-suite-debug-log',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        if(class_exists(ProtectedOwner::class)&&ProtectedOwner::isEnabled()&&!ProtectedOwner::isProtectedCurrentUser()){
            wp_die(esc_html__('Only a protected owner can view the debug log.','smg-site-suite'));
        }

        $path=defined('WP_DEBUG_LOG')&&is_string(WP_DEBUG_LOG)?WP_DEBUG_LOG:WP_CONTENT_DIR.'/debug.log';
        echo '<div class="wrap"><h1>'.esc_html__('Debug Log Viewer','smg-site-suite').'</h1>';
        echo '<p><code>'.esc_html($path).'</code></p>';

        if(!is_readable($path)){
            echo '<div class="notice notice-info"><p>'.esc_html__('No readable WordPress debug log was found.','smg-site-suite').'</p></div></div>';
            return;
        }

        $lines=$this->tail($path,500);
        echo '<p>'.esc_html__('Showing the latest 500 lines. Debug logs may contain sensitive information.','smg-site-suite').'</p>';
        echo '<pre style="max-height:70vh;overflow:auto;background:#111;color:#eee;padding:16px;white-space:pre-wrap">'.esc_html(implode("",$lines)).'</pre></div>';
    }

    private function tail(string $path,int $maxLines):array{
        $file=new \SplFileObject($path,'r');
        $file->seek(PHP_INT_MAX);
        $last=$file->key();
        $start=max(0,$last-$maxLines+1);
        $lines=[];
        $file->seek($start);
        while(!$file->eof()){
            $lines[]=$file->fgets();
        }
        return $lines;
    }
}
