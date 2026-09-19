<?php
namespace SMG\SiteSuite\Modules\Security;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableFileEditing implements ModuleInterface {
    public function register():void{
        add_action('admin_init',[$this,'blockEditor']);
    }
    public function blockEditor():void{
        global $pagenow;
        if(in_array($pagenow,['theme-editor.php','plugin-editor.php'],true)){
            wp_safe_redirect(admin_url());
            exit;
        }
        remove_submenu_page('themes.php','theme-editor.php');
        remove_submenu_page('plugins.php','plugin-editor.php');
    }
}
