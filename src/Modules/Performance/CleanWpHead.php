<?php
namespace SMG\SiteSuite\Modules\Performance;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class CleanWpHead implements ModuleInterface {
    public function register():void{
        remove_action('wp_head','rsd_link');
        remove_action('wp_head','wlwmanifest_link');
        remove_action('wp_head','wp_shortlink_wp_head',10);
        remove_action('template_redirect','wp_shortlink_header',11);
        remove_action('wp_head','adjacent_posts_rel_link_wp_head',10);
    }
}
