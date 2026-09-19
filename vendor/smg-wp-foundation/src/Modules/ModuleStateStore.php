<?php
namespace SMG\WPFoundation\Modules;
final class ModuleStateStore {
    private string $optionName;
    public function __construct(string $optionName){$this->optionName=$optionName;}
    public function active():array{$value=get_option($this->optionName,[]);return is_array($value)?array_values(array_unique(array_filter(array_map('sanitize_key',$value)))):[];}
    public function isActive(string $slug):bool{return in_array($slug,$this->active(),true);}
    public function setActive(array $slugs):void{update_option($this->optionName,array_values(array_unique(array_filter(array_map('sanitize_key',$slugs)))),false);}
    public function activate(string $slug):void{$active=$this->active();$active[]=sanitize_key($slug);$this->setActive($active);}
    public function deactivate(string $slug):void{$this->setActive(array_values(array_diff($this->active(),[sanitize_key($slug)])));}
}
