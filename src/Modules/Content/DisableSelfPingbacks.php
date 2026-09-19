<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableSelfPingbacks implements ModuleInterface {
    public function register():void{add_action('pre_ping',[$this,'filterLinks']);}
    public function filterLinks(array &$links):void{
        $home=wp_parse_url(home_url('/'),PHP_URL_HOST);
        foreach($links as $key=>$link){
            if(wp_parse_url($link,PHP_URL_HOST)===$home)unset($links[$key]);
        }
    }
}
