<?php
namespace SMG\WPFoundation\Modules;
use InvalidArgumentException;
final class ModuleDefinition {
    private string $slug; private string $name; private string $description; private string $category; private string $className;
    private array $tags; private array $dependencies; private array $contexts; private string $risk; private bool $hasSettings; private bool $defaultEnabled;
    public function __construct(array $definition){
        foreach(['slug','name','description','category','class'] as $required){
            if(!isset($definition[$required])||!is_string($definition[$required])||trim($definition[$required])==='') throw new InvalidArgumentException('Missing or invalid module definition field: '.$required);
        }
        $slug=strtolower(trim($definition['slug']));
        if(!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$slug)) throw new InvalidArgumentException('Module slug must be lowercase kebab-case.');
        $risk=strtolower((string)($definition['risk']??'low'));
        if(!in_array($risk,['low','medium','high','destructive'],true)) throw new InvalidArgumentException('Unsupported module risk level.');
        $this->slug=$slug;$this->name=trim($definition['name']);$this->description=trim($definition['description']);$this->category=strtolower(trim($definition['category']));
        $this->className=trim($definition['class']);$this->tags=array_values(array_unique(array_filter(array_map('strval',$definition['tags']??[]))));
        $this->dependencies=is_array($definition['dependencies']??null)?$definition['dependencies']:[];$this->contexts=array_values(array_unique(array_filter(array_map('strval',$definition['contexts']??['all']))));
        $this->risk=$risk;$this->hasSettings=(bool)($definition['has_settings']??false);$this->defaultEnabled=(bool)($definition['default_enabled']??false);
    }
    public function slug():string{return $this->slug;} public function name():string{return $this->name;} public function description():string{return $this->description;}
    public function category():string{return $this->category;} public function className():string{return $this->className;} public function tags():array{return $this->tags;}
    public function dependencies():array{return $this->dependencies;} public function contexts():array{return $this->contexts;} public function risk():string{return $this->risk;}
    public function hasSettings():bool{return $this->hasSettings;} public function defaultEnabled():bool{return $this->defaultEnabled;}
}
