<?php
namespace SMG\SiteSuite\Modules\Media;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class UploadSizeLimit implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_upload_size_limit';

    public function register():void{
        add_filter('wp_handle_upload_prefilter',[$this,'check']);
    }

    public function settingsSchema():array{
        return [['key'=>'megabytes','type'=>'number','label'=>__('Maximum upload size (MB)','smg-site-suite'),'default'=>10,'description'=>__('Set 0 to use the server/WordPress limit only.','smg-site-suite')]];
    }

    public function settings():array{
        $v=get_option(self::OPTION,['megabytes'=>10]);
        return is_array($v)?$v:[];
    }

    public function saveSettings(array $input):void{
        update_option(self::OPTION,['megabytes'=>max(0,min(2048,absint($input['megabytes']??10)))],false);
    }

    public function check(array $file):array{
        $mb=(int)($this->settings()['megabytes']??0);
        if($mb<=0)return $file;
        $limit=$mb*MB_IN_BYTES;
        if((int)($file['size']??0)>$limit){
            $file['error']=sprintf(__('This file exceeds the Site Suite upload limit of %d MB.','smg-site-suite'),$mb);
        }
        return $file;
    }
}
