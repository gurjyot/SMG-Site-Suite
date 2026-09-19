<?php
namespace SMG\WPFoundation\Modules;
final class ModuleInstaller {
    public function __construct(private ModuleRegistry $registry,private ModuleStateStore $state,private string $installedOption){}
    public function initializeDefaultsOnce():void{if((bool)get_option($this->installedOption,false))return;$defaults=[];foreach($this->registry->all() as $m)if($m->defaultEnabled())$defaults[]=$m->slug();$this->state->setActive($defaults);update_option($this->installedOption,1,false);}
}
