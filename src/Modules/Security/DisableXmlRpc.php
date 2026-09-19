<?php
namespace SMG\SiteSuite\Modules\Security;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableXmlRpc implements ModuleInterface {
    public function register():void{
        add_filter('xmlrpc_enabled','__return_false');
        add_filter('wp_headers',[$this,'headers']);
        add_filter('bloginfo_url',[$this,'removePingbackUrl'],10,2);
    }
    public function headers(array $headers):array{unset($headers['X-Pingback']);return $headers;}
    public function removePingbackUrl(string $output,string $show):string{return $show==='pingback_url'?'':$output;}
}
