<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class ExternalLinksNewTab implements ModuleInterface {
    public function register():void{
        add_action('wp_enqueue_scripts',[$this,'script']);
    }
    public function script():void{
        if(is_admin())return;
        wp_register_script('smg-site-suite-external-links','',[],SMG_SITE_SUITE_VERSION,true);
        wp_enqueue_script('smg-site-suite-external-links');
        $host=wp_parse_url(home_url('/'),PHP_URL_HOST);
        $js='document.addEventListener("click",function(e){const a=e.target.closest("a[href]");if(!a)return;try{const u=new URL(a.href,location.href);if(u.protocol!=="http:"&&u.protocol!=="https:")return;if(u.host&&u.host!=='.wp_json_encode((string)$host).'){a.target="_blank";a.rel="noopener noreferrer";}}catch(_){}});';
        wp_add_inline_script('smg-site-suite-external-links',$js);
    }
}
