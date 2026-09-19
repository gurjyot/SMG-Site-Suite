<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class AdminTaxonomyFilters implements ModuleInterface {
    public function register():void{
        add_action('restrict_manage_posts',[$this,'filters'],20,2);
    }

    public function filters(string $postType,string $which):void{
        if($which!=='top')return;
        $taxonomies=get_object_taxonomies($postType,'objects');
        foreach($taxonomies as $taxonomy){
            if(empty($taxonomy->show_ui)||!$taxonomy->hierarchical)continue;
            $selected=isset($_GET[$taxonomy->query_var])?sanitize_text_field(wp_unslash($_GET[$taxonomy->query_var])):'';
            wp_dropdown_categories([
                'taxonomy'=>$taxonomy->name,
                'name'=>$taxonomy->query_var,
                'show_option_all'=>sprintf(__('All %s','smg-site-suite'),$taxonomy->labels->name),
                'hide_empty'=>false,
                'hierarchical'=>true,
                'value_field'=>'slug',
                'selected'=>$selected,
            ]);
        }
    }
}
