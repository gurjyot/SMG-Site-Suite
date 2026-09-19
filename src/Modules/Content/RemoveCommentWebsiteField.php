<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class RemoveCommentWebsiteField implements ModuleInterface {
    public function register():void{add_filter('comment_form_default_fields',[$this,'fields']);}
    public function fields(array $fields):array{unset($fields['url']);return $fields;}
}
