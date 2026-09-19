<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class HideAdminNotices implements ModuleInterface {
    public function register():void{
        add_action('admin_head',static function():void{
            if(!current_user_can('manage_options'))return;
            echo '<style>.notice:not(.smg-site-suite-keep),.update-nag{display:none!important}</style>';
        },999);
    }
}
