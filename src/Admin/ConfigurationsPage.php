<?php
namespace SMG\SiteSuite\Admin;

use SMG\WPFoundation\Modules\ModuleManager;
use RuntimeException;

final class ConfigurationsPage {
    public function __construct(private ModuleManager $manager){}

    public function boot():void{
        add_action('admin_menu',[$this,'menu'],20);
        add_action('admin_post_smg_site_suite_apply_preset',[$this,'applyPreset']);
        add_action('admin_post_smg_site_suite_export_config',[$this,'export']);
        add_action('admin_post_smg_site_suite_import_config',[$this,'import']);
    }

    public function menu():void{
        add_submenu_page(
            'smg-site-suite',
            __('Configurations','smg-site-suite'),
            __('Configurations','smg-site-suite'),
            'manage_options',
            'smg-site-suite-configurations',
            [$this,'render']
        );
    }

    public static function presets():array{
        return [
            'starter'=>[
                'name'=>__('Starter Pack','smg-site-suite'),
                'description'=>__('Common cleanup, security, and admin tools for a new site.','smg-site-suite'),
                'modules'=>['disable-emojis','disable-embeds','disable-xml-rpc','disable-dashicons-frontend','hide-wp-version','disable-application-passwords','disable-file-editing','show-ids','active-plugins-first','featured-image-column'],
            ],
            'clean-wordpress'=>[
                'name'=>__('Clean WordPress','smg-site-suite'),
                'description'=>__('Remove common WordPress frontend and admin bloat.','smg-site-suite'),
                'modules'=>['disable-emojis','disable-embeds','disable-xml-rpc','disable-dashicons-frontend','clean-wp-head','disable-admin-bar-frontend'],
            ],
            'security'=>[
                'name'=>__('Security Basics','smg-site-suite'),
                'description'=>__('Basic WordPress hardening without changing the login flow.','smg-site-suite'),
                'modules'=>['hide-wp-version','disable-xml-rpc','disable-application-passwords','disable-file-editing','security-headers','generic-login-errors'],
            ],
            'agency-client-handoff'=>[
                'name'=>__('Agency Client Handoff','smg-site-suite'),
                'description'=>__('Protect the agency account, simplify the client dashboard, and apply the usual handoff branding.','smg-site-suite'),
                'modules'=>['protected-owner','admin-menu-organizer','custom-dashboard-page','login-branding','admin-footer','dashboard-widgets','hide-admin-notices','environment-indicator','activity-log-lite'],
            ],
            'performance'=>[
                'name'=>__('Performance Basics','smg-site-suite'),
                'description'=>__('Remove common frontend assets and reduce background admin work.','smg-site-suite'),
                'modules'=>['disable-emojis','disable-embeds','disable-dashicons-frontend','clean-wp-head','heartbeat-control','revision-control'],
            ],
            'admin-productivity'=>[
                'name'=>__('Admin Productivity','smg-site-suite'),
                'description'=>__('Add the admin columns and dashboard changes we use most often.','smg-site-suite'),
                'modules'=>['show-ids','active-plugins-first','featured-image-column','dashboard-widgets','footer-timezone','admin-footer'],
            ],
            'woo-store-basics'=>[
                'name'=>__('Woo Store Basics','smg-site-suite'),
                'description'=>__('Enable the WooCommerce tools we commonly use on client stores.','smg-site-suite'),
                'modules'=>['payment-method-column','order-phone-column','buy-now','shipping-progress','free-shipping-only','cod-rules','order-amount-rules','woocommerce-wishlist','whatsapp-enquiry','product-tabs-control','disable-marketplace-suggestions'],
            ],
        ];
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        $active=$this->manager->state()->active();
        echo '<div class="wrap"><h1>'.esc_html__('Site Suite Configurations','smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Presets add modules without turning anything off. You can also export or import the active module list; module settings stay unchanged.','smg-site-suite').'</p>';
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;max-width:1100px">';
        foreach(self::presets() as $slug=>$preset){
            $url=wp_nonce_url(admin_url('admin-post.php?action=smg_site_suite_apply_preset&preset='.rawurlencode($slug)),'smg_site_suite_preset_'.$slug);
            echo '<div class="card" style="max-width:none"><h2>'.esc_html($preset['name']).'</h2><p>'.esc_html($preset['description']).'</p><p><strong>'.count($preset['modules']).'</strong> '.esc_html__('modules','smg-site-suite').'</p><a class="button button-primary" href="'.esc_url($url).'">'.esc_html__('Apply Preset','smg-site-suite').'</a></div>';
        }
        echo '</div>';

        echo '<hr style="margin:28px 0"><h2>'.esc_html__('Transfer Configuration','smg-site-suite').'</h2>';
        echo '<p>'.esc_html(sprintf(__('Currently %d modules are active.','smg-site-suite'),count($active))).'</p>';
        $export=wp_nonce_url(admin_url('admin-post.php?action=smg_site_suite_export_config'),'smg_site_suite_export_config');
        echo '<p><a class="button" href="'.esc_url($export).'">'.esc_html__('Export Active Modules','smg-site-suite').'</a></p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="smg_site_suite_import_config">';
        wp_nonce_field('smg_site_suite_import_config');
        echo '<input type="file" name="config_file" accept=".json,application/json" required> ';
        submit_button(__('Import Active Modules','smg-site-suite'),'secondary','submit',false);
        echo '</form></div>';
    }

    public function applyPreset():void{
        $this->authorize();
        $slug=isset($_GET['preset'])?sanitize_key(wp_unslash($_GET['preset'])):'';
        check_admin_referer('smg_site_suite_preset_'.$slug);
        $presets=self::presets();
        if(!isset($presets[$slug]))wp_die(esc_html__('Unknown preset.','smg-site-suite'));

        $active=$this->manager->state()->active();
        foreach($presets[$slug]['modules'] as $module){
            $status=$this->manager->status($module);
            if(!$status['known']||!$status['available']||in_array($module,$active,true))continue;
            try{$this->manager->activate($module);}catch(RuntimeException $e){continue;}
        }
        $this->redirectNotice('preset');
    }

    public function export():void{
        $this->authorize();
        check_admin_referer('smg_site_suite_export_config');
        $payload=[
            'schema'=>1,
            'product'=>'smg-site-suite',
            'exported_at'=>gmdate('c'),
            'active_modules'=>$this->manager->state()->active(),
        ];
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="smg-site-suite-config-'.gmdate('Y-m-d').'.json"');
        echo wp_json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function import():void{
        $this->authorize();
        check_admin_referer('smg_site_suite_import_config');
        $tmpName=isset($_FILES['config_file']['tmp_name'])
            ? sanitize_text_field(wp_unslash((string)$_FILES['config_file']['tmp_name']))
            : '';
        if($tmpName===''||!is_uploaded_file($tmpName))wp_die(esc_html__('No valid configuration file uploaded.','smg-site-suite'));

        $raw=file_get_contents($tmpName);
        $payload=is_string($raw)?json_decode($raw,true):null;
        if(!is_array($payload)||(int)($payload['schema']??0)!==1||($payload['product']??'')!=='smg-site-suite')wp_die(esc_html__('Invalid Site Suite configuration file.','smg-site-suite'));

        $requested=is_array($payload['active_modules']??null)?array_values(array_unique(array_map('sanitize_key',$payload['active_modules']))):[];
        $known=array_keys($this->manager->registry()->all());
        $accepted=array_values(array_intersect($requested,$known));
        $current=$this->manager->state()->active();

        foreach(array_values(array_diff($current,$accepted)) as $slug){
            try{$this->manager->deactivate($slug);}catch(RuntimeException $e){continue;}
        }

        foreach(array_values(array_diff($accepted,$current)) as $slug){
            $status=$this->manager->status($slug);
            if(!$status['known']||!$status['available'])continue;
            try{$this->manager->activate($slug);}catch(RuntimeException $e){continue;}
        }

        $this->redirectNotice('import');
    }

    private function authorize():void{
        if(!current_user_can('manage_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
    }

    private function redirectNotice(string $type):void{
        wp_safe_redirect(add_query_arg(['page'=>'smg-site-suite-configurations','smg_notice'=>$type],admin_url('admin.php')));
        exit;
    }
}
