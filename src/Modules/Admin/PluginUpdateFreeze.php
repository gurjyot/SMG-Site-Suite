<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;
use WP_Error;

final class PluginUpdateFreeze implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_frozen_plugins';

    public function register():void{
        add_filter('auto_update_plugin',[$this,'autoUpdate'],99,2);
        add_filter('upgrader_pre_install',[$this,'preInstall'],99,2);
        add_filter('plugin_action_links',[$this,'actions'],99,4);
    }

    public function settingsSchema():array{
        require_once ABSPATH.'wp-admin/includes/plugin.php';
        $options=[];
        foreach(get_plugins() as $file=>$plugin)$options[$file]=(string)($plugin['Name']??$file);
        return [['key'=>'plugins','type'=>'multiselect','label'=>__('Frozen plugins','smg-site-suite'),'options'=>$options,'description'=>__('Frozen plugins cannot auto-update. Manual updates are blocked for other administrators; Protected Owner users can still update them intentionally.','smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,['plugins'=>[]]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $plugins=isset($input['plugins'])&&is_array($input['plugins'])?array_values(array_unique(array_map('sanitize_text_field',$input['plugins']))):[];
        update_option(self::OPTION,['plugins'=>$plugins],false);
    }

    public function autoUpdate($update,$item){
        $file=isset($item->plugin)?(string)$item->plugin:'';
        return $file!==''&&in_array($file,$this->frozen(),true)?false:$update;
    }

    public function preInstall($response,array $hookExtra){
        if(is_wp_error($response))return $response;
        if(ProtectedOwner::isEnabled()&&ProtectedOwner::isProtectedCurrentUser())return $response;

        $plugin=(string)($hookExtra['plugin']??'');
        $plugins=isset($hookExtra['plugins'])&&is_array($hookExtra['plugins'])?$hookExtra['plugins']:[];
        $targets=$plugin!==''?[$plugin]:$plugins;
        foreach($targets as $target){
            if(in_array((string)$target,$this->frozen(),true))return new WP_Error('smg_plugin_frozen',__('This plugin is frozen by SMG Site Suite. A Protected Owner must perform the update.','smg-site-suite'));
        }
        return $response;
    }

    public function actions(array $actions,string $pluginFile,array $pluginData,string $context):array{
        if(!in_array($pluginFile,$this->frozen(),true))return $actions;
        if(ProtectedOwner::isEnabled()&&ProtectedOwner::isProtectedCurrentUser())return $actions;
        unset($actions['update']);
        return $actions;
    }

    private function frozen():array{return array_values(array_filter((array)($this->settings()['plugins']??[]),'is_string'));}
}
