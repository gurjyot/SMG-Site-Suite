<?php
namespace SMG\SiteSuite\Modules\Users;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class HideAdminBarByRole implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_hide_admin_bar_roles';

    public function register():void{add_filter('show_admin_bar',[$this,'filter']);}

    public function settingsSchema():array{
        global $wp_roles;
        $options=[];
        foreach(($wp_roles?->roles??[]) as $slug=>$role)$options[$slug]=(string)($role['name']??$slug);
        return [['key'=>'roles','type'=>'multiselect','label'=>__('Hide frontend admin bar for roles','smg-site-suite'),'options'=>$options]];
    }

    public function settings():array{$v=get_option(self::OPTION,['roles'=>[]]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $roles=isset($input['roles'])&&is_array($input['roles'])?array_values(array_unique(array_map('sanitize_key',$input['roles']))):[];
        update_option(self::OPTION,['roles'=>$roles],false);
    }

    public function filter(bool $show):bool{
        if(!is_user_logged_in())return $show;
        $roles=(array)wp_get_current_user()->roles;
        return array_intersect($roles,(array)($this->settings()['roles']??[]))===[]?$show:false;
    }
}
