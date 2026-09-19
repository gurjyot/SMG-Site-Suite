<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class InternalContentNotes implements ModuleInterface {
    private const META='_smg_site_suite_internal_note';

    public function register():void{
        add_action('add_meta_boxes',[$this,'boxes']);
        add_action('save_post',[$this,'save']);
        add_filter('manage_posts_columns',[$this,'column']);
        add_action('manage_posts_custom_column',[$this,'value'],10,2);
        add_filter('manage_pages_columns',[$this,'column']);
        add_action('manage_pages_custom_column',[$this,'value'],10,2);
    }

    public function boxes():void{
        foreach(get_post_types(['show_ui'=>true],'names') as $type){
            if($type==='attachment')continue;
            add_meta_box('smg-site-suite-internal-note',__('Internal Note','smg-site-suite'),[$this,'render'],$type,'side','default');
        }
    }

    public function render(\WP_Post $post):void{
        $value=(string)get_post_meta($post->ID,self::META,true);
        wp_nonce_field('smg_site_suite_internal_note_'.$post->ID,'smg_site_suite_internal_note_nonce');
        echo '<textarea class="widefat" rows="5" name="smg_internal_note">'.esc_textarea($value).'</textarea>';
        echo '<p class="description">'.esc_html__('Visible only in wp-admin.','smg-site-suite').'</p>';
    }

    public function save(int $postId):void{
        if(!isset($_POST['smg_site_suite_internal_note_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_site_suite_internal_note_nonce'])),'smg_site_suite_internal_note_'.$postId))return;
        if(!current_user_can('edit_post',$postId))return;
        $value=isset($_POST['smg_internal_note'])
            ? sanitize_textarea_field(wp_unslash($_POST['smg_internal_note']))
            : '';
        if($value==='')delete_post_meta($postId,self::META);else update_post_meta($postId,self::META,$value);
    }

    public function column(array $columns):array{
        $columns['smg_internal_note']=__('Note','smg-site-suite');
        return $columns;
    }

    public function value(string $column,int $postId):void{
        if($column!=='smg_internal_note')return;
        $note=(string)get_post_meta($postId,self::META,true);
        echo $note!==''?esc_html(wp_trim_words($note,8,'…')):'—';
    }
}
