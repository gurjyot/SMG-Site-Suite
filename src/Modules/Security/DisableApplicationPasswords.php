<?php
namespace SMG\SiteSuite\Modules\Security;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableApplicationPasswords implements ModuleInterface {
    public function register():void{add_filter('wp_is_application_passwords_available','__return_false');}
}
