<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class AutoPublishMissedSchedules implements ModuleInterface {
    public function register():void{add_action('init',[$this,'recover'],20);}
    public function recover():void{
        if(wp_doing_ajax()||wp_doing_cron())return;
        $posts=get_posts([
            'post_type'=>'any','post_status'=>'future','posts_per_page'=>5,
            'orderby'=>'date','order'=>'ASC','date_query'=>[['column'=>'post_date_gmt','before'=>gmdate('Y-m-d H:i:s')]],
            'fields'=>'ids','no_found_rows'=>true,'suppress_filters'=>false,
        ]);
        foreach($posts as $postId){
            if(get_post_status($postId)!=='future')continue;
            wp_publish_post((int)$postId);
        }
    }
}
