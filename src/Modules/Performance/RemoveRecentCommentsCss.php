<?php
namespace SMG\SiteSuite\Modules\Performance;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class RemoveRecentCommentsCss implements ModuleInterface {
    public function register():void{
        add_action('widgets_init',static function():void{global $wp_widget_factory;if(isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments']))remove_action('wp_head',[$wp_widget_factory->widgets['WP_Widget_Recent_Comments'],'recent_comments_style']);},20);
    }
}
