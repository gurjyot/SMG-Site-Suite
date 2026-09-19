<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CustomOrderStatuses implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_order_statuses';

    public function register():void{
        add_action('init',[$this,'registerStatuses'],20);
        add_filter('wc_order_statuses',[$this,'statuses'],20);
    }

    public function settingsSchema():array{
        return [['key'=>'statuses','type'=>'textarea','label'=>__('Custom order statuses','smg-site-suite'),'description'=>__("One per line: slug=Label\nExample: packed=Packed",'smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['statuses'=>sanitize_textarea_field((string)($input['statuses']??''))],false);}

    public function registerStatuses():void{
        foreach($this->map() as $slug=>$label){
            register_post_status('wc-'.$slug,[
                'label'=>$label,
                'public'=>true,
                'exclude_from_search'=>false,
                'show_in_admin_all_list'=>true,
                'show_in_admin_status_list'=>true,
                'label_count'=>_n_noop($label.' <span class="count">(%s)</span>',$label.' <span class="count">(%s)</span>','smg-site-suite'),
            ]);
        }
    }

    public function statuses(array $statuses):array{
        foreach($this->map() as $slug=>$label)$statuses['wc-'.$slug]=$label;
        return $statuses;
    }

    private function map():array{
        $out=[];$raw=(string)($this->settings()['statuses']??'');
        foreach(preg_split('/\r\n|\r|\n/',$raw)?:[] as $line){
            if(!str_contains($line,'='))continue;
            [$slug,$label]=array_map('trim',explode('=',$line,2));
            $slug=sanitize_title($slug);$label=sanitize_text_field($label);
            if($slug!==''&&$label!=='')$out[$slug]=$label;
        }
        return $out;
    }
}
