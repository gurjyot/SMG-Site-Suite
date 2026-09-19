<?php
namespace SMG\SiteSuite\Modules\Media;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class MediaDetailsColumns implements ModuleInterface {
    public function register():void{
        add_filter('manage_media_columns',[$this,'columns']);
        add_action('manage_media_custom_column',[$this,'value'],10,2);
    }

    public function columns(array $columns):array{
        $columns['smg_dimensions']=__('Dimensions','smg-site-suite');
        $columns['smg_filesize']=__('File Size','smg-site-suite');
        return $columns;
    }

    public function value(string $column,int $attachmentId):void{
        if($column==='smg_dimensions'){
            $meta=wp_get_attachment_metadata($attachmentId);
            if(is_array($meta)&&!empty($meta['width'])&&!empty($meta['height']))echo esc_html((int)$meta['width'].' × '.(int)$meta['height']);
            else echo '—';
            return;
        }

        if($column==='smg_filesize'){
            $file=get_attached_file($attachmentId);
            echo esc_html(is_string($file)&&is_file($file)?size_format((int)filesize($file),2):'—');
        }
    }
}
