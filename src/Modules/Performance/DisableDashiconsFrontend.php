<?php
namespace SMG\SiteSuite\Modules\Performance;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableDashiconsFrontend implements ModuleInterface {
    public function register():void{
        add_action('wp_enqueue_scripts',[$this,'dequeue'],100);
    }
    public function dequeue():void{
        if(is_user_logged_in())return;
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}
