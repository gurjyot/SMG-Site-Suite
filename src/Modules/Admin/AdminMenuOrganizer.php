<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class AdminMenuOrganizer implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_admin_menu';

    public function register():void{add_action('admin_menu',[$this,'apply'],999);}

    public function settingsSchema():array{
        return [['key'=>'hide','type'=>'textarea','label'=>__('Hide top-level admin menu slugs','smg-site-suite'),'description'=>__("One menu slug per line, e.g. edit-comments.php\ntools.php",'smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['hide'=>sanitize_textarea_field((string)($input['hide']??''))],false);}

    public function apply():void{
        if(!current_user_can('manage_options'))return;
        foreach(preg_split('/\r\n|\r|\n/',(string)($this->settings()['hide']??''))?:[] as $slug){
            $slug=trim($slug);if($slug!=='')remove_menu_page($slug);
        }
    }
}
