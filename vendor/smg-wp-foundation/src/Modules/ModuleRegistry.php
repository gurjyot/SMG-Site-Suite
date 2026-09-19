<?php
namespace SMG\WPFoundation\Modules;
use InvalidArgumentException;
final class ModuleRegistry {
    private array $modules=[]; private array $categories=[];
    public function addCategory(string $slug,string $label):self{$slug=strtolower(trim($slug));if($slug===''||$label==='')throw new InvalidArgumentException('Category slug and label are required.');$this->categories[$slug]=$label;return $this;}
    public function register(ModuleDefinition $module):self{if(isset($this->modules[$module->slug()]))throw new InvalidArgumentException('Duplicate module slug: '.$module->slug());$this->modules[$module->slug()]=$module;return $this;}
    public function get(string $slug):?ModuleDefinition{return $this->modules[$slug]??null;} public function all():array{return $this->modules;} public function categories():array{return $this->categories;}
}
