<?php
namespace SMG\WPFoundation\Data;
use InvalidArgumentException;
final class DataOwnershipRegistry {
    private array $owners=[];
    public function register(string $module,string $type,string $key,string $policy='preserve'):self{
        $module=sanitize_key($module);$type=sanitize_key($type);$key=trim($key);$policy=sanitize_key($policy);
        if($module===''||$type===''||$key==='')throw new InvalidArgumentException('Module, data type, and key are required.');
        if(!in_array($type,['option','site_option','user_meta','post_meta','term_meta','table','cron','transient','file'],true))throw new InvalidArgumentException('Unsupported data ownership type.');
        if(!in_array($policy,['preserve','generated','configuration','user_data'],true))throw new InvalidArgumentException('Unsupported data ownership policy.');
        $this->owners[$module][]=['type'=>$type,'key'=>$key,'policy'=>$policy];return $this;
    }
    public function module(string $module):array{return $this->owners[sanitize_key($module)]??[];}
    public function all():array{return $this->owners;}
    public function removableFor(string $module):array{return array_values(array_filter($this->module($module),static fn(array $item):bool=>in_array($item['policy'],['generated','configuration'],true)));}
}
