<?php
namespace SMG\SiteSuite\Modules\Security;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableAuthorArchives implements ModuleInterface {
    public function register():void{
        add_action('template_redirect',[$this,'redirect']);
        add_filter('author_link',[$this,'authorLink'],10,3);
    }
    public function redirect():void{
        if(!is_author())return;
        wp_safe_redirect(home_url('/'),301);
        exit;
    }
    public function authorLink(string $link,int $authorId,string $nicename):string{
        return home_url('/');
    }
}
