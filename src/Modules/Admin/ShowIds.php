<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class ShowIds implements ModuleInterface {
    public function register():void{
        foreach(get_post_types(['show_ui'=>true],'names') as $type){
            add_filter("manage_{$type}_posts_columns",[$this,'column']);
            add_action("manage_{$type}_posts_custom_column",[$this,'value'],10,2);
        }
        foreach(get_taxonomies(['show_ui'=>true],'names') as $taxonomy){
            add_filter("manage_edit-{$taxonomy}_columns",[$this,'column']);
            add_filter("manage_{$taxonomy}_custom_column",[$this,'termValue'],10,3);
        }
    }
    public function column(array $columns):array{$columns['smg_id']=__('ID','smg-site-suite');return $columns;}
    public function value(string $column,int $id):void{if($column==='smg_id')echo esc_html((string)$id);}
    public function termValue(string $value,string $column,int $termId):string{return $column==='smg_id'?(string)$termId:$value;}
}
