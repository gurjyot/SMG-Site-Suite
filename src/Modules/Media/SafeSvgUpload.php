<?php
namespace SMG\SiteSuite\Modules\Media;
use DOMDocument;use DOMElement;use SMG\WPFoundation\Contracts\ModuleInterface;
final class SafeSvgUpload implements ModuleInterface {
    private const ELEMENTS=['svg','g','path','rect','circle','ellipse','line','polyline','polygon','text','tspan','defs','linearGradient','radialGradient','stop','clipPath','mask','title','desc'];
    private const ATTRS=['xmlns','viewBox','width','height','x','y','x1','y1','x2','y2','cx','cy','r','rx','ry','d','points','fill','fill-opacity','stroke','stroke-width','stroke-linecap','stroke-linejoin','stroke-opacity','opacity','transform','id','class','offset','stop-color','stop-opacity','font-family','font-size','font-weight','text-anchor','clip-path','mask'];
    public function register():void{
        add_filter('upload_mimes',[$this,'mimes']);add_filter('wp_check_filetype_and_ext',[$this,'filetype'],10,5);add_filter('wp_handle_upload_prefilter',[$this,'sanitizeUpload']);
    }
    public function mimes(array $mimes):array{if(current_user_can('upload_files'))$mimes['svg']='image/svg+xml';return $mimes;}
    public function filetype(array $data,string $file,string $filename,array $mimes,string|false $realMime=''):array{
        if(strtolower(pathinfo($filename,PATHINFO_EXTENSION))==='svg'&&current_user_can('upload_files'))return ['ext'=>'svg','type'=>'image/svg+xml','proper_filename'=>$data['proper_filename']??false];
        return $data;
    }
    public function sanitizeUpload(array $file):array{
        if(strtolower(pathinfo((string)($file['name']??''),PATHINFO_EXTENSION))!=='svg')return $file;
        if(!current_user_can('upload_files')){$file['error']=__('You are not allowed to upload SVG files.','smg-site-suite');return $file;}
        $contents=@file_get_contents((string)$file['tmp_name']);$clean=is_string($contents)?$this->sanitize($contents):false;
        if($clean===false||@file_put_contents((string)$file['tmp_name'],$clean)===false)$file['error']=__('The SVG could not be safely sanitized.','smg-site-suite');
        return $file;
    }
    private function sanitize(string $svg):string|false{
        if(!class_exists(DOMDocument::class)||stripos($svg,'<!DOCTYPE')!==false||stripos($svg,'<!ENTITY')!==false)return false;
        $previous=libxml_use_internal_errors(true);$dom=new DOMDocument();
        $loaded=$dom->loadXML($svg,LIBXML_NONET|LIBXML_NOBLANKS);libxml_clear_errors();libxml_use_internal_errors($previous);
        if(!$loaded||!$dom->documentElement||strtolower($dom->documentElement->localName)!=='svg')return false;
        $this->cleanNode($dom->documentElement);
        $out=$dom->saveXML($dom->documentElement);return is_string($out)?$out:false;
    }
    private function cleanNode(DOMElement $node):void{
        foreach(iterator_to_array($node->childNodes) as $child){
            if($child instanceof DOMElement){
                if(!in_array($child->localName,self::ELEMENTS,true)){$node->removeChild($child);continue;}
                $this->cleanNode($child);
            }
        }
        foreach(iterator_to_array($node->attributes) as $attr){
            $name=$attr->nodeName;$value=$attr->nodeValue;
            if(str_starts_with(strtolower($name),'on')||!in_array($name,self::ATTRS,true)||preg_match('/(?:javascript:|data:text\/html|https?:\/\/|\/\/)/i',(string)$value))$node->removeAttributeNode($attr);
        }
    }
}
