<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class AdminMenuOrganizer implements ModuleInterface {
    private const OPTION='smg_site_suite_admin_menu_tree';
    private const SEP='__smg_sub__';

    public function register():void{
        add_action('admin_menu',[$this,'registerPage'],50);
        add_action('admin_menu',[$this,'apply'],999999);
        add_action('admin_post_smg_site_suite_save_admin_menu',[$this,'save']);
    }

    public function registerPage():void{
        if(!ProtectedOwner::isEnabled()||ProtectedOwner::isProtectedCurrentUser()){
            add_submenu_page(
                'smg-site-suite',
                __('Menu Organizer','smg-site-suite'),
                __('Menu Organizer','smg-site-suite'),
                'manage_options',
                'smg-site-suite-menu-organizer',
                [$this,'render']
            );
        }
    }

    public function apply():void{
        if(ProtectedOwner::isEnabled()&&ProtectedOwner::isProtectedCurrentUser())return;

        $settings=$this->settings();

        foreach((array)($settings['hidden_main']??[]) as $slug){
            $slug=(string)$slug;
            if($slug!==''&&$slug!=='index.php')remove_menu_page($slug);
        }

        foreach((array)($settings['hidden_sub']??[]) as $encoded){
            $parts=explode(self::SEP,(string)$encoded,2);
            if(count($parts)!==2)continue;
            [$parent,$child]=$parts;
            if($parent!==''&&$child!=='')remove_submenu_page($parent,$child);
        }
    }

    private function settings():array{
        $value=get_option(self::OPTION,[]);
        return is_array($value)?$value:[];
    }

    public function render():void{
        if(ProtectedOwner::isEnabled()&&!ProtectedOwner::isProtectedCurrentUser()){
            wp_die(esc_html__('Only a protected owner can configure the admin menu.','smg-site-suite'));
        }
        if(!current_user_can('manage_options'))return;

        global $menu,$submenu;
        $settings=$this->settings();
        $hiddenMain=(array)($settings['hidden_main']??[]);
        $hiddenSub=(array)($settings['hidden_sub']??[]);

        echo '<div class="wrap"><h1>'.esc_html__('Admin Menu Organizer','smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Choose exactly which wp-admin menus and submenus ordinary administrators should see. Protected owners always retain the full menu.','smg-site-suite').'</p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="smg_site_suite_save_admin_menu">';
        wp_nonce_field('smg_site_suite_save_admin_menu');

        echo '<div style="max-width:980px">';
        foreach((array)$menu as $item){
            if(!isset($item[0],$item[2])||$item[0]==='')continue;
            $slug=(string)$item[2];
            if($slug==='smg-site-suite')continue;

            $label=wp_strip_all_tags((string)$item[0]);
            $visible=!in_array($slug,$hiddenMain,true);
            $locked=$slug==='index.php';

            echo '<section style="margin:0 0 12px;border:1px solid #dcdcde;border-radius:10px;background:#fff;overflow:hidden">';
            echo '<input type="hidden" name="all_main[]" value="'.esc_attr($slug).'">';
            echo '<label style="display:flex;align-items:center;gap:10px;padding:14px 16px;font-weight:600;background:#f6f7f7">';
            echo '<input type="checkbox" name="visible_main[]" value="'.esc_attr($slug).'" '.checked($visible,true,false).' '.disabled($locked,true,false).'> '.esc_html($label);
            if($locked)echo '<span style="font-weight:400;color:#646970">'.esc_html__('(required)','smg-site-suite').'</span>';
            echo '</label>';

            if(!empty($submenu[$slug])&&is_array($submenu[$slug])){
                echo '<div style="padding:8px 16px 12px 42px">';
                foreach($submenu[$slug] as $subItem){
                    if(!isset($subItem[0],$subItem[2]))continue;
                    $child=(string)$subItem[2];
                    $subLabel=wp_strip_all_tags((string)$subItem[0]);
                    $encoded=$slug.self::SEP.$child;
                    $subVisible=!in_array($encoded,$hiddenSub,true);
                    echo '<input type="hidden" name="all_sub[]" value="'.esc_attr($encoded).'">';
                    echo '<label style="display:block;padding:6px 0"><input type="checkbox" name="visible_sub[]" value="'.esc_attr($encoded).'" '.checked($subVisible,true,false).'> '.esc_html($subLabel).'</label>';
                }
                echo '</div>';
            }
            echo '</section>';
        }
        echo '</div>';
        submit_button(__('Save Admin Menu','smg-site-suite'));
        echo '</form></div>';
    }

    public function save():void{
        if(ProtectedOwner::isEnabled()&&!ProtectedOwner::isProtectedCurrentUser()){
            wp_die(esc_html__('Only a protected owner can configure the admin menu.','smg-site-suite'));
        }
        if(!current_user_can('manage_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_save_admin_menu');

        $visibleMain=isset($_POST['visible_main'])&&is_array($_POST['visible_main'])?array_map('sanitize_text_field',wp_unslash($_POST['visible_main'])):[];
        $visibleSub=isset($_POST['visible_sub'])&&is_array($_POST['visible_sub'])?array_map('sanitize_text_field',wp_unslash($_POST['visible_sub'])):[];
        $allMain=isset($_POST['all_main'])&&is_array($_POST['all_main'])?array_map('sanitize_text_field',wp_unslash($_POST['all_main'])):[];
        $allSub=isset($_POST['all_sub'])&&is_array($_POST['all_sub'])?array_map('sanitize_text_field',wp_unslash($_POST['all_sub'])):[];

        $hiddenMain=[];$hiddenSub=[];

        foreach($allMain as $slug){
            if($slug==='smg-site-suite'||$slug==='index.php'||$slug==='')continue;
            if(!in_array($slug,$visibleMain,true))$hiddenMain[]=$slug;
        }

        foreach($allSub as $encoded){
            if($encoded===''||!str_contains($encoded,self::SEP))continue;
            if(!in_array($encoded,$visibleSub,true))$hiddenSub[]=$encoded;
        }

        update_option(self::OPTION,[
            'hidden_main'=>array_values(array_unique($hiddenMain)),
            'hidden_sub'=>array_values(array_unique($hiddenSub)),
        ],false);

        wp_safe_redirect(add_query_arg(['page'=>'smg-site-suite-menu-organizer','updated'=>'1'],admin_url('admin.php')));
        exit;
    }
}
