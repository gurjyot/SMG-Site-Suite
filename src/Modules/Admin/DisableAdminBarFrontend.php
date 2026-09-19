<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableAdminBarFrontend implements ModuleInterface {
    public function register():void{add_filter('show_admin_bar',static fn(bool $show):bool=>is_admin()?$show:false);}
}
