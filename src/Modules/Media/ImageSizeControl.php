<?php
namespace SMG\SiteSuite\Modules\Media;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class ImageSizeControl implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_image_size_control';
    public function register():void{add_filter('intermediate_image_sizes_advanced',[$this,'filterSizes']);}
    public function settingsSchema():array{return [
        ['key'=>'medium_large','type'=>'checkbox','label'=>__('Disable medium_large size','smg-site-suite')],
        ['key'=>'1536x1536','type'=>'checkbox','label'=>__('Disable 1536×1536 size','smg-site-suite')],
        ['key'=>'2048x2048','type'=>'checkbox','label'=>__('Disable 2048×2048 size','smg-site-suite')]
    ];}
    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'medium_large'=>!empty($input['medium_large']),
            '1536x1536'=>!empty($input['1536x1536']),
            '2048x2048'=>!empty($input['2048x2048'])
        ],false);
    }
    public function filterSizes(array $sizes):array{
        $s=$this->settings();
        foreach(['medium_large','1536x1536','2048x2048'] as $key){
            if(!empty($s[$key]))unset($sizes[$key]);
        }
        return $sizes;
    }
}
