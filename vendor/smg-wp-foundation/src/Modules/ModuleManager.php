<?php
namespace SMG\WPFoundation\Modules;
use RuntimeException;use SMG\WPFoundation\Contracts\ActivatableModuleInterface;use SMG\WPFoundation\Contracts\DeactivatableModuleInterface;use SMG\WPFoundation\Contracts\ModuleInterface;use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class ModuleManager {
    public function __construct(private ModuleRegistry $registry,private ModuleStateStore $state,private DependencyChecker $dependencies){}
    public function registry():ModuleRegistry{return $this->registry;} public function state():ModuleStateStore{return $this->state;}
    public function status(string $slug):array{$m=$this->registry->get($slug);if(!$m)return ['known'=>false,'active'=>false,'available'=>false,'missing'=>[]];$d=$this->dependencies->check($m);return ['known'=>true,'active'=>$this->state->isActive($slug),'available'=>$d['available'],'missing'=>$d['missing']];}
    public function activate(string $slug):void{$m=$this->required($slug);$d=$this->dependencies->check($m);if(!$d['available'])throw new RuntimeException('Missing dependencies: '.implode(', ',$d['missing']));$i=$this->instance($m);if($i instanceof ActivatableModuleInterface)$i->activate();$this->state->activate($slug);}
    public function deactivate(string $slug):void{$m=$this->required($slug);$i=$this->instance($m);if($i instanceof DeactivatableModuleInterface)$i->deactivate();$this->state->deactivate($slug);}
    public function settingsModule(string $slug):SettingsModuleInterface{$m=$this->required($slug);$i=$this->instance($m);if(!$i instanceof SettingsModuleInterface)throw new RuntimeException('Module has no settings contract.');return $i;}
    private function required(string $slug):ModuleDefinition{$m=$this->registry->get($slug);if(!$m)throw new RuntimeException('Unknown module: '.$slug);return $m;}
    private function instance(ModuleDefinition $m):ModuleInterface{$class=$m->className();if(!class_exists($class))throw new RuntimeException('Module class not found.');$i=new $class();if(!$i instanceof ModuleInterface)throw new RuntimeException('Invalid module contract.');return $i;}
}
