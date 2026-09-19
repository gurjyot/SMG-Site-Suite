<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class RemoveCommentWebsiteField implements ModuleInterface {
    public function register():void{
        add_filter('comment_form_default_fields',[$this,'removeUrlField']);
    }

    public function removeUrlField(array $fields):array{
        return array_diff_key($fields,['url'=>true]);
    }
}
