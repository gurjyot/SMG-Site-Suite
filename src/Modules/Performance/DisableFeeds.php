<?php
namespace SMG\SiteSuite\Modules\Performance;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableFeeds implements ModuleInterface {
    public function register():void{
        foreach(['do_feed','do_feed_rdf','do_feed_rss','do_feed_rss2','do_feed_atom','do_feed_rss2_comments','do_feed_atom_comments'] as $hook){
            add_action($hook,[$this,'blocked'],1);
        }
        remove_action('wp_head','feed_links',2);
        remove_action('wp_head','feed_links_extra',3);
    }
    public function blocked():void{
        wp_die(
            esc_html__('Feeds are disabled on this site.','smg-site-suite'),
            esc_html__('Feed disabled','smg-site-suite'),
            ['response'=>410]
        );
    }
}
