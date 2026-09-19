<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableTexturize implements ModuleInterface {
    public function register():void{
        add_filter('run_wptexturize','__return_false');
    }
}
