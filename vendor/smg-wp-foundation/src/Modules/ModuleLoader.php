<?php
namespace SMG\WPFoundation\Modules;
use SMG\WPFoundation\Contracts\ModuleInterface; use Throwable;
final class ModuleLoader {
    private ModuleRegistry $registry;private ModuleStateStore $state;private DependencyChecker $dependencies;private ContextChecker $contexts;private array $loaded=[];private array $failures=[];
    public function __construct(ModuleRegistry $registry,ModuleStateStore $state,DependencyChecker $dependencies,?ContextChecker $contexts=null){$this->registry=$registry;$this->state=$state;$this->dependencies=$dependencies;$this->contexts=$contexts??new ContextChecker();}
    public function loadActive():void{foreach($this->state->active() as $slug)$this->load($slug);do_action('smg_wp_foundation_modules_loaded',array_keys($this->loaded),$this->failures);}
    public function load(string $slug):bool{
        if(isset($this->loaded[$slug]))return true;$definition=$this->registry->get($slug);if(!$definition){$this->failures[$slug]=['reason'=>'unknown_module'];return false;}
        if(!$this->contexts->allows($definition)){$this->failures[$slug]=['reason'=>'context_not_allowed'];return false;}
        $deps=$this->dependencies->check($definition);if(!$deps['available']){$this->failures[$slug]=['reason'=>'missing_dependencies','missing'=>$deps['missing']];return false;}
        try{$class=$definition->className();if(!class_exists($class))return false;$instance=new $class();if(!$instance instanceof ModuleInterface)return false;$instance->register();$this->loaded[$slug]=$instance;return true;}catch(Throwable $e){$this->failures[$slug]=['reason'=>'exception','message'=>$e->getMessage()];return false;}
    }
}
