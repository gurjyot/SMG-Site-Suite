<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class ReadingTime implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_reading_time';

    public function register():void{
        add_shortcode('smg_reading_time',[$this,'shortcode']);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'words_per_minute','type'=>'number','label'=>__('Words per minute','smg-site-suite'),'default'=>225],
            ['key'=>'label','type'=>'text','label'=>__('Label format','smg-site-suite'),'default'=>'{{minutes}} min read'],
        ];
    }

    public function settings():array{
        $v=get_option(self::OPTION,['words_per_minute'=>225,'label'=>'{{minutes}} min read']);
        return is_array($v)?$v:[];
    }

    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'words_per_minute'=>max(100,min(600,absint($input['words_per_minute']??225))),
            'label'=>sanitize_text_field((string)($input['label']??'{{minutes}} min read')),
        ],false);
    }

    public function shortcode(array $atts=[]):string{
        $postId=absint($atts['post_id']??get_the_ID());
        $post=get_post($postId);if(!$post)return '';
        $words=str_word_count(wp_strip_all_tags(strip_shortcodes($post->post_content)));
        $wpm=(int)($this->settings()['words_per_minute']??225);
        $minutes=max(1,(int)ceil($words/$wpm));
        return esc_html(str_replace('{{minutes}}',(string)$minutes,(string)($this->settings()['label']??'{{minutes}} min read')));
    }
}
