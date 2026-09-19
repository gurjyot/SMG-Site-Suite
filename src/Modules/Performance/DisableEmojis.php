<?php
namespace SMG\SiteSuite\Modules\Performance;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DisableEmojis implements ModuleInterface {
    public function register():void{
        $actions=[
            ['wp_head','print_emoji_detection_script',7],
            ['admin_print_scripts','print_emoji_detection_script',10],
            ['wp_print_styles','print_emoji_styles',10],
            ['admin_print_styles','print_emoji_styles',10],
        ];

        foreach($actions as [$hook,$callback,$priority]){
            remove_action($hook,$callback,$priority);
        }

        $filters=[
            ['the_content_feed','wp_staticize_emoji'],
            ['comment_text_rss','wp_staticize_emoji'],
            ['wp_mail','wp_staticize_emoji_for_email'],
        ];

        foreach($filters as [$hook,$callback]){
            remove_filter($hook,$callback);
        }

        add_filter('tiny_mce_plugins',[$this,'removeEditorPlugin']);
        add_filter('wp_resource_hints',[$this,'removeEmojiDnsPrefetch'],10,2);
    }

    public function removeEditorPlugin($plugins):array{
        if(!is_array($plugins))return [];
        return array_values(array_diff($plugins,['wpemoji']));
    }

    public function removeEmojiDnsPrefetch(array $urls,string $relation):array{
        if($relation!=='dns-prefetch')return $urls;

        return array_values(array_filter(
            $urls,
            static fn($url):bool=>!str_contains((string)$url,'s.w.org/images/core/emoji/')
        ));
    }
}
