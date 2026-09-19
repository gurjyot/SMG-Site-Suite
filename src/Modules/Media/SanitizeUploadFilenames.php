<?php
namespace SMG\SiteSuite\Modules\Media;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class SanitizeUploadFilenames implements ModuleInterface {
    public function register():void{
        add_filter('sanitize_file_name',[$this,'sanitize'],20);
    }

    public function sanitize(string $filename):string{
        $info=pathinfo($filename);
        $name=(string)($info['filename']??'file');
        $ext=isset($info['extension'])?'.'.strtolower((string)$info['extension']):'';
        $name=remove_accents($name);
        $name=strtolower($name);
        $name=preg_replace('/[^a-z0-9]+/','-',$name)??'file';
        $name=trim($name,'-');
        return ($name!==''?$name:'file').$ext;
    }
}
