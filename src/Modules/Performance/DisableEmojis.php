<?php
namespace SMG\SiteSuite\Modules\Performance;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableEmojis implements ModuleInterface {
    public function register():void{
        remove_action('wp_head','print_emoji_detection_script',7);
        remove_action('admin_print_scripts','print_emoji_detection_script');
        remove_action('wp_print_styles','print_emoji_styles');
        remove_action('admin_print_styles','print_emoji_styles');
        remove_filter('the_content_feed','wp_staticize_emoji');
        remove_filter('comment_text_rss','wp_staticize_emoji');
        remove_filter('wp_mail','wp_staticize_emoji_for_email');
        add_filter('tiny_mce_plugins',static function($plugins){
            if(!is_array($plugins))return [];
            return array_values(array_diff($plugins,['wpemoji']));
        });
        add_filter('wp_resource_hints',static function(array $urls,string $relation):array{
            if($relation!=='dns-prefetch')return $urls;
            return array_values(array_filter($urls,static fn($url)=>!str_contains((string)$url,'s.w.org/images/core/emoji/')));
        },10,2);
    }
}
