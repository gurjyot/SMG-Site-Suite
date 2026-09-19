<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DuplicateNavigationMenu implements ModuleInterface {
    public function register():void{
        add_action('admin_menu',[$this,'menu'],90);
        add_action('admin_post_smg_site_suite_duplicate_menu',[$this,'duplicate']);
    }

    public function menu():void{
        add_submenu_page('themes.php',__('Duplicate Menu','smg-site-suite'),__('Duplicate Menu','smg-site-suite'),'edit_theme_options','smg-site-suite-duplicate-menu',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('edit_theme_options'))return;
        $menus=wp_get_nav_menus();
        echo '<div class="wrap"><h1>'.esc_html__('Duplicate Navigation Menu','smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Duplicates classic WordPress navigation menus and their menu items.','smg-site-suite').'</p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="smg_site_suite_duplicate_menu">';
        wp_nonce_field('smg_site_suite_duplicate_menu');
        echo '<table class="form-table"><tr><th><label>'.esc_html__('Source menu','smg-site-suite').'</label></th><td><select name="menu_id" required><option value="">'.esc_html__('Select menu','smg-site-suite').'</option>';
        foreach($menus as $menu)echo '<option value="'.esc_attr((string)$menu->term_id).'">'.esc_html($menu->name).'</option>';
        echo '</select></td></tr><tr><th><label>'.esc_html__('New menu name','smg-site-suite').'</label></th><td><input class="regular-text" type="text" name="new_name" required></td></tr></table>';
        submit_button(__('Duplicate Menu','smg-site-suite'));
        echo '</form></div>';
    }

    public function duplicate():void{
        if(!current_user_can('edit_theme_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_duplicate_menu');

        $sourceId=absint($_POST['menu_id']??0);
        $name=sanitize_text_field((string)($_POST['new_name']??''));
        if($sourceId<=0||$name==='')wp_die(esc_html__('Source menu and new name are required.','smg-site-suite'));

        $newMenuId=wp_create_nav_menu($name);
        if(is_wp_error($newMenuId))wp_die(esc_html($newMenuId->get_error_message()));

        $items=wp_get_nav_menu_items($sourceId,['post_status'=>'any']);
        $map=[];
        foreach((array)$items as $item){
            $parent=isset($map[$item->menu_item_parent])?$map[$item->menu_item_parent]:0;
            $newItem=wp_update_nav_menu_item($newMenuId,0,[
                'menu-item-title'=>$item->title,
                'menu-item-description'=>$item->description,
                'menu-item-attr-title'=>$item->attr_title,
                'menu-item-target'=>$item->target,
                'menu-item-classes'=>implode(' ',(array)$item->classes),
                'menu-item-xfn'=>$item->xfn,
                'menu-item-url'=>$item->url,
                'menu-item-status'=>'publish',
                'menu-item-parent-id'=>$parent,
                'menu-item-object-id'=>$item->object_id,
                'menu-item-object'=>$item->object,
                'menu-item-type'=>$item->type,
            ]);
            if(!is_wp_error($newItem))$map[$item->ID]=$newItem;
        }

        wp_safe_redirect(add_query_arg('menu',$newMenuId,admin_url('nav-menus.php')));
        exit;
    }
}
