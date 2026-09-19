<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class PluginThemeNotes implements ModuleInterface {
    private const OPTION='smg_site_suite_component_notes';

    public function register():void{
        add_action('admin_menu',[$this,'menu'],90);
        add_action('admin_post_smg_site_suite_save_component_notes',[$this,'save']);
    }

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Plugin & Theme Notes','smg-site-suite'),__('Plugin & Theme Notes','smg-site-suite'),'manage_options','smg-site-suite-component-notes',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        require_once ABSPATH.'wp-admin/includes/plugin.php';
        $plugins=get_plugins();
        $themes=wp_get_themes();
        $notes=get_option(self::OPTION,[]);if(!is_array($notes))$notes=[];

        echo '<div class="wrap"><h1>'.esc_html__('Plugin & Theme Notes','smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Store internal agency notes such as why a component is installed, client-specific constraints, or update cautions.','smg-site-suite').'</p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="smg_site_suite_save_component_notes">';
        wp_nonce_field('smg_site_suite_save_component_notes');

        echo '<h2>'.esc_html__('Plugins','smg-site-suite').'</h2><table class="widefat striped"><thead><tr><th>'.esc_html__('Plugin','smg-site-suite').'</th><th>'.esc_html__('Note','smg-site-suite').'</th></tr></thead><tbody>';
        foreach($plugins as $file=>$plugin){
            $key='plugin:'.$file;
            echo '<tr><td><strong>'.esc_html((string)($plugin['Name']??$file)).'</strong><br><code>'.esc_html($file).'</code></td><td><textarea class="widefat" rows="2" name="notes['.esc_attr(base64_encode($key)).']">'.esc_textarea((string)($notes[$key]??'')).'</textarea></td></tr>';
        }
        echo '</tbody></table>';

        echo '<h2 style="margin-top:24px">'.esc_html__('Themes','smg-site-suite').'</h2><table class="widefat striped"><thead><tr><th>'.esc_html__('Theme','smg-site-suite').'</th><th>'.esc_html__('Note','smg-site-suite').'</th></tr></thead><tbody>';
        foreach($themes as $slug=>$theme){
            $key='theme:'.$slug;
            echo '<tr><td><strong>'.esc_html($theme->get('Name')).'</strong><br><code>'.esc_html($slug).'</code></td><td><textarea class="widefat" rows="2" name="notes['.esc_attr(base64_encode($key)).']">'.esc_textarea((string)($notes[$key]??'')).'</textarea></td></tr>';
        }
        echo '</tbody></table>';
        submit_button(__('Save Notes','smg-site-suite'));
        echo '</form></div>';
    }

    public function save():void{
        if(!current_user_can('manage_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_save_component_notes');
        $submitted=isset($_POST['notes'])&&is_array($_POST['notes'])?wp_unslash($_POST['notes']):[];
        $notes=[];
        foreach($submitted as $encoded=>$note){
            $key=base64_decode((string)$encoded,true);
            if(!is_string($key)||(!str_starts_with($key,'plugin:')&&!str_starts_with($key,'theme:')))continue;
            $value=sanitize_textarea_field((string)$note);
            if($value!=='')$notes[$key]=$value;
        }
        update_option(self::OPTION,$notes,false);
        wp_safe_redirect(add_query_arg(['page'=>'smg-site-suite-component-notes','updated'=>'1'],admin_url('admin.php')));
        exit;
    }
}
