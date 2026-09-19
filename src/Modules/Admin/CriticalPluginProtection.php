<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CriticalPluginProtection implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_critical_plugins';

    public function register():void{
        add_filter('plugin_action_links',[$this,'actions'],99,4);
        add_filter('pre_update_option_active_plugins',[$this,'protect'],99,2);
        add_filter('auto_update_plugin',[$this,'disableAutoUpdate'],99,2);
    }

    public function settingsSchema():array{
        require_once ABSPATH.'wp-admin/includes/plugin.php';
        $options=[];
        foreach(get_plugins() as $file=>$plugin)$options[$file]=(string)($plugin['Name']??$file);
        return [['key'=>'plugins','type'=>'multiselect','label'=>__('Protected plugins','smg-site-suite'),'options'=>$options,'description'=>__('Protected plugins cannot be deactivated by ordinary administrators and will not auto-update. Protected Owner users may still deactivate them.','smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,['plugins'=>[]]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $plugins=isset($input['plugins'])&&is_array($input['plugins'])?array_values(array_unique(array_map('sanitize_text_field',$input['plugins']))):[];
        update_option(self::OPTION,['plugins'=>$plugins],false);
    }

    public function actions(array $actions,string $pluginFile,array $pluginData,string $context):array{
        if(in_array($pluginFile,$this->protected(),true)&&!(ProtectedOwner::isEnabled()&&ProtectedOwner::isProtectedCurrentUser()))unset($actions['deactivate']);
        return $actions;
    }

    public function protect($new,$old){
        if(ProtectedOwner::isEnabled()&&ProtectedOwner::isProtectedCurrentUser())return $new;
        $newList=is_array($new)?$new:[];$oldList=is_array($old)?$old:[];
        foreach($this->protected() as $plugin){
            if(in_array($plugin,$oldList,true)&&!in_array($plugin,$newList,true))$newList[]=$plugin;
        }
        return array_values(array_unique($newList));
    }

    public function disableAutoUpdate($update,$item){
        $file=isset($item->plugin)?(string)$item->plugin:'';
        return $file!==''&&in_array($file,$this->protected(),true)?false:$update;
    }

    private function protected():array{return array_values(array_filter((array)($this->settings()['plugins']??[]),'is_string'));}
}
