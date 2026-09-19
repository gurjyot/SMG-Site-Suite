<?php
namespace SMG\SiteSuite\Modules\Media;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class ReplaceMedia implements ModuleInterface {
    public function register():void{
        add_filter('media_row_actions',[$this,'rowAction'],20,3);
        add_action('admin_menu',[$this,'menu'],80);
        add_action('admin_post_smg_site_suite_replace_media',[$this,'replace']);
    }

    public function menu():void{
        add_media_page(
            __('Replace Media','smg-site-suite'),
            __('Replace Media','smg-site-suite'),
            'upload_files',
            'smg-site-suite-replace-media',
            [$this,'render']
        );
    }

    public function rowAction(array $actions,\WP_Post $post,$detached):array{
        if(!current_user_can('edit_post',$post->ID)||$post->post_type!=='attachment')return $actions;
        $url=add_query_arg(['page'=>'smg-site-suite-replace-media','attachment_id'=>$post->ID],admin_url('upload.php'));
        $actions['smg_replace_media']='<a href="'.esc_url($url).'">'.esc_html__('Replace','smg-site-suite').'</a>';
        return $actions;
    }

    public function render():void{
        if(!current_user_can('upload_files'))return;
        $attachmentId=isset($_GET['attachment_id'])?absint($_GET['attachment_id']):0;
        $attachment=$attachmentId?get_post($attachmentId):null;

        echo '<div class="wrap"><h1>'.esc_html__('Replace Media File','smg-site-suite').'</h1>';
        if(isset($_GET['replaced']))echo '<div class="notice notice-success"><p>'.esc_html__('Media file replaced successfully.','smg-site-suite').'</p></div>';

        if(!$attachment||$attachment->post_type!=='attachment'){
            echo '<p>'.esc_html__('Choose an attachment from Media Library and use its Replace action.','smg-site-suite').'</p></div>';
            return;
        }

        echo '<p><strong>'.esc_html__('Attachment:','smg-site-suite').'</strong> '.esc_html($attachment->post_title).' (#'.esc_html((string)$attachmentId).')</p>';
        echo '<p>'.wp_get_attachment_image($attachmentId,'medium').'</p>';
        echo '<form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'">';
        echo '<input type="hidden" name="action" value="smg_site_suite_replace_media"><input type="hidden" name="attachment_id" value="'.esc_attr((string)$attachmentId).'">';
        wp_nonce_field('smg_site_suite_replace_media_'.$attachmentId);
        echo '<table class="form-table"><tr><th><label>'.esc_html__('Replacement file','smg-site-suite').'</label></th><td><input type="file" name="replacement_file" required></td></tr></table>';
        echo '<p class="description">'.esc_html__('The attachment ID, title, caption, alt text, description, and references stay unchanged. Generated image sizes are rebuilt for image replacements.','smg-site-suite').'</p>';
        submit_button(__('Replace File','smg-site-suite'));
        echo '</form></div>';
    }

    public function replace():void{
        $attachmentId=isset($_POST['attachment_id'])?absint($_POST['attachment_id']):0;
        if($attachmentId<=0||!current_user_can('edit_post',$attachmentId)||!current_user_can('upload_files'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_replace_media_'.$attachmentId);

        $attachment=get_post($attachmentId);
        if(!$attachment||$attachment->post_type!=='attachment')wp_die(esc_html__('Invalid attachment.','smg-site-suite'));
        if(empty($_FILES['replacement_file']['tmp_name']))wp_die(esc_html__('No replacement file uploaded.','smg-site-suite'));

        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/image.php';

        $oldFile=get_attached_file($attachmentId);
        $oldMeta=wp_get_attachment_metadata($attachmentId);

        $upload=wp_handle_upload($_FILES['replacement_file'],['test_form'=>false]);
        if(isset($upload['error']))wp_die(esc_html((string)$upload['error']));

        $newFile=(string)$upload['file'];
        $newUrl=(string)$upload['url'];
        $newType=(string)$upload['type'];

        update_attached_file($attachmentId,$newFile);
        wp_update_post(['ID'=>$attachmentId,'post_mime_type'=>$newType,'guid'=>$newUrl]);

        $newMeta=wp_generate_attachment_metadata($attachmentId,$newFile);
        if(is_array($newMeta))wp_update_attachment_metadata($attachmentId,$newMeta);

        $this->deleteOldFiles($oldFile,$oldMeta,$newFile);

        wp_safe_redirect(add_query_arg(['page'=>'smg-site-suite-replace-media','attachment_id'=>$attachmentId,'replaced'=>'1'],admin_url('upload.php')));
        exit;
    }

    private function deleteOldFiles($oldFile,$oldMeta,string $newFile):void{
        if(!is_string($oldFile)||$oldFile===''||$oldFile===$newFile)return;

        $paths=[$oldFile];
        if(is_array($oldMeta)&&!empty($oldMeta['sizes'])&&is_array($oldMeta['sizes'])){
            $dir=dirname($oldFile);
            foreach($oldMeta['sizes'] as $size){
                if(!empty($size['file']))$paths[]=$dir.'/'.basename((string)$size['file']);
            }
        }

        foreach(array_unique($paths) as $path){
            if($path!==$newFile&&is_file($path))wp_delete_file($path);
        }
    }
}
