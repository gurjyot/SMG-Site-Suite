<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class ContentMetricsColumns implements ModuleInterface {
    public function register():void{
        foreach(get_post_types(['show_ui'=>true],'names') as $type){
            if($type==='attachment')continue;
            add_filter("manage_{$type}_posts_columns",[$this,'columns']);
            add_action("manage_{$type}_posts_custom_column",[$this,'value'],10,2);
        }
    }

    public function columns(array $columns):array{
        $columns['smg_word_count']=__('Words','smg-site-suite');
        $columns['smg_modified']=__('Modified','smg-site-suite');
        return $columns;
    }

    public function value(string $column,int $postId):void{
        $post=get_post($postId);if(!$post)return;

        if($column==='smg_word_count'){
            $count=str_word_count(wp_strip_all_tags(strip_shortcodes($post->post_content)));
            echo esc_html(number_format_i18n($count));
        }

        if($column==='smg_modified'){
            echo esc_html(get_the_modified_date(get_option('date_format').' '.get_option('time_format'),$postId));
        }
    }
}
