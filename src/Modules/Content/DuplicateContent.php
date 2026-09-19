<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DuplicateContent implements ModuleInterface {
    public function register():void{
        add_filter('post_row_actions',[$this,'rowAction'],10,2);add_filter('page_row_actions',[$this,'rowAction'],10,2);add_action('admin_action_smg_site_suite_duplicate',[$this,'duplicate']);
    }
    public function rowAction(array $actions,WP_Post $post):array{
        if(!current_user_can('edit_post',$post->ID)||wp_is_post_revision($post->ID)||wp_is_post_autosave($post->ID))return $actions;
        $url=wp_nonce_url(admin_url('admin.php?action=smg_site_suite_duplicate&post='.$post->ID),'smg_site_suite_duplicate_'.$post->ID);
        $actions['smg_duplicate']='<a href="'.esc_url($url).'">'.esc_html__('Duplicate','smg-site-suite').'</a>';return $actions;
    }
    public function duplicate():void{
        $id=isset($_GET['post'])?absint($_GET['post']):0;if(!$id||!current_user_can('edit_post',$id))wp_die(esc_html__('You cannot duplicate this content.','smg-site-suite'));
        check_admin_referer('smg_site_suite_duplicate_'.$id);$source=get_post($id);if(!$source)wp_die(esc_html__('Content not found.','smg-site-suite'));
        $newId=wp_insert_post(['post_author'=>get_current_user_id(),'post_content'=>$source->post_content,'post_excerpt'=>$source->post_excerpt,'post_name'=>'','post_parent'=>$source->post_parent,'post_password'=>$source->post_password,'post_status'=>'draft','post_title'=>$source->post_title.' '.__('(Copy)','smg-site-suite'),'post_type'=>$source->post_type,'menu_order'=>$source->menu_order,'comment_status'=>$source->comment_status,'ping_status'=>$source->ping_status]);
        if(is_wp_error($newId))wp_die(esc_html($newId->get_error_message()));
        foreach(get_post_meta($id) as $key=>$values){if(in_array($key,['_edit_lock','_edit_last'],true))continue;foreach($values as $value)add_post_meta($newId,$key,maybe_unserialize($value));}
        foreach(get_object_taxonomies($source->post_type) as $tax){$terms=wp_get_object_terms($id,$tax,['fields'=>'ids']);if(!is_wp_error($terms))wp_set_object_terms($newId,$terms,$tax);}
        wp_safe_redirect(get_edit_post_link($newId,'raw'));exit;
    }
}
