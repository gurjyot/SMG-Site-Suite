<?php
namespace SMG\SiteSuite\Modules\Performance;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DisableEmbeds implements ModuleInterface {
    public function register():void{
        foreach(['wp_oembed_add_discovery_links','wp_oembed_add_host_js'] as $callback){
            remove_action('wp_head',$callback);
        }

        add_filter('embed_oembed_discover',[$this,'disableDiscovery']);
        add_filter('rewrite_rules_array',[$this,'removeEmbedRules']);
        add_filter('rest_endpoints',[$this,'removeOembedRoutes']);
    }

    public function disableDiscovery():bool{
        return false;
    }

    public function removeEmbedRules(array $rules):array{
        return $this->withoutPrefix($rules,'embed/');
    }

    public function removeOembedRoutes(array $endpoints):array{
        return $this->withoutPrefix($endpoints,'/oembed/');
    }

    private function withoutPrefix(array $items,string $prefix):array{
        foreach($items as $key=>$value){
            if(str_starts_with((string)$key,$prefix))unset($items[$key]);
        }

        return $items;
    }
}
