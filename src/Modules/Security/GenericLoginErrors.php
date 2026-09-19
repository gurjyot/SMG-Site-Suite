<?php
namespace SMG\SiteSuite\Modules\Security;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class GenericLoginErrors implements ModuleInterface {
    public function register():void{add_filter('login_errors',[$this,'message']);}
    public function message():string{return __('Invalid login details.','smg-site-suite');}
}
