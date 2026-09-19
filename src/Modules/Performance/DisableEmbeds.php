<?php
namespace SMG\SiteSuite\Modules\Performance;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableEmbeds implements ModuleInterface {
    public function register():void{
        remove_action('wp_head','wp_oembed_add_discovery_links');
        remove_action('wp_head','wp_oembed_add_host_js');
        add_filter('embed_oembed_discover','__return_false');
        add_filter('rewrite_rules_array',[$this,'rewriteRules']);
        add_filter('rest_endpoints',[$this,'restEndpoints']);
    }
    public function rewriteRules(array $rules):array{
        foreach(array_keys($rules) as $rule){
            if(str_starts_with($rule,'embed/'))unset($rules[$rule]);
        }
        return $rules;
    }
    public function restEndpoints(array $endpoints):array{
        foreach(array_keys($endpoints) as $route){
            if(str_starts_with($route,'/oembed/'))unset($endpoints[$route]);
        }
        return $endpoints;
    }
}
