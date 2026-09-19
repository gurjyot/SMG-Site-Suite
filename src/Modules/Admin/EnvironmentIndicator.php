<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class EnvironmentIndicator implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_environment_indicator';

    public function register():void{
        add_action('admin_bar_menu',[$this,'bar'],9999);
        add_action('admin_head',[$this,'style']);
        add_action('wp_head',[$this,'style']);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'label','type'=>'text','label'=>__('Environment label','smg-site-suite'),'default'=>'Production'],
            ['key'=>'color','type'=>'text','label'=>__('Indicator color','smg-site-suite'),'default'=>'#b32d2e','description'=>__('Hex color, e.g. #b32d2e.','smg-site-suite')],
            ['key'=>'frontend','type'=>'checkbox','label'=>__('Show in frontend admin bar','smg-site-suite'),'default'=>true],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,['label'=>'Production','color'=>'#b32d2e','frontend'=>true]);return is_array($v)?$v:[];}

    public function saveSettings(array $input):void{
        $color=sanitize_hex_color((string)($input['color']??''))?:'#b32d2e';
        update_option(self::OPTION,[
            'label'=>sanitize_text_field((string)($input['label']??'Production')),
            'color'=>$color,
            'frontend'=>!empty($input['frontend']),
        ],false);
    }

    public function bar(\WP_Admin_Bar $bar):void{
        if(!is_admin()&&empty($this->settings()['frontend']))return;
        $label=(string)($this->settings()['label']??'Production');
        if($label==='')return;
        $bar->add_node(['id'=>'smg-site-suite-environment','title'=>esc_html($label),'href'=>false]);
    }

    public function style():void{
        if(!is_admin()&&empty($this->settings()['frontend']))return;
        $color=(string)($this->settings()['color']??'#b32d2e');
        echo '<style>#wpadminbar #wp-admin-bar-smg-site-suite-environment>.ab-item{background:'.esc_attr($color).'!important;color:#fff!important;font-weight:600}</style>';
    }
}
