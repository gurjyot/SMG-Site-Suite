<?php
namespace SMG\SiteSuite\Modules\Performance;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DisableFeeds implements ModuleInterface {
    private const FEED_HOOKS=[
        'do_feed',
        'do_feed_rdf',
        'do_feed_rss',
        'do_feed_rss2',
        'do_feed_atom',
        'do_feed_rss2_comments',
        'do_feed_atom_comments',
    ];

    public function register():void{
        foreach(self::FEED_HOOKS as $hook){
            add_action($hook,[$this,'renderDisabledFeed'],1);
        }

        foreach([['feed_links',2],['feed_links_extra',3]] as [$callback,$priority]){
            remove_action('wp_head',$callback,$priority);
        }
    }

    public function renderDisabledFeed():void{
        wp_die(
            esc_html__('This site does not publish feeds.','smg-site-suite'),
            esc_html__('Feed unavailable','smg-site-suite'),
            ['response'=>410]
        );
    }
}
