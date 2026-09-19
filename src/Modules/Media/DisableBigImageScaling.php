<?php
namespace SMG\SiteSuite\Modules\Media;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableBigImageScaling implements ModuleInterface {
    public function register():void{add_filter('big_image_size_threshold','__return_false');}
}
