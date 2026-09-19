<?php
namespace SMG\WPFoundation\Admin;
use SMG\WPFoundation\Modules\ModuleManager;use Throwable;
final class ModuleBrowser {
    private string $ajaxAction;
    public function __construct(private ModuleManager $manager,private array $config){
        $this->config=array_merge(['page_title'=>'Modules','menu_title'=>'Modules','menu_slug'=>'smg-modules','capability'=>'manage_options','icon'=>'dashicons-screenoptions','position'=>58,'asset_url'=>'','version'=>'1.0.0'],$config);
        $this->ajaxAction='smg_foundation_toggle_'.sanitize_key((string)$this->config['menu_slug']);
    }
    public function boot():void{add_action('admin_menu',[$this,'registerMenu']);add_action('admin_enqueue_scripts',[$this,'assets']);add_action('wp_ajax_'.$this->ajaxAction,[$this,'toggle']);}
    public function registerMenu():void{add_menu_page((string)$this->config['page_title'],(string)$this->config['menu_title'],(string)$this->config['capability'],(string)$this->config['menu_slug'],[$this,'render'],(string)$this->config['icon'],(int)$this->config['position']);}
    public function assets(string $hook):void{
        if($hook!=='toplevel_page_'.$this->config['menu_slug'])return;$base=rtrim((string)$this->config['asset_url'],'/').'/';
        wp_enqueue_style('smg-foundation-module-browser',$base.'module-browser.css',[],(string)$this->config['version']);
        wp_enqueue_script('smg-foundation-module-browser',$base.'module-browser.js',[],(string)$this->config['version'],true);
        wp_localize_script('smg-foundation-module-browser','smgFoundationModules',['ajaxUrl'=>admin_url('admin-ajax.php'),'action'=>$this->ajaxAction,'nonce'=>wp_create_nonce($this->ajaxAction)]);
    }
    public function render():void{
        if(!current_user_can((string)$this->config['capability']))return;
        $settingsSlug=isset($_GET['module_settings'])?sanitize_key(wp_unslash($_GET['module_settings'])):'';
        if($settingsSlug!==''){$this->renderSettings($settingsSlug);return;}
        $categories=$this->manager->registry()->categories();$modules=$this->manager->registry()->all();
        echo '<div class="wrap smg-foundation-modules"><div class="smg-foundation-header"><div><h1>'.esc_html((string)$this->config['page_title']).'</h1><p>'.esc_html__('Enable only the tools this site needs.','smg-site-suite').'</p></div><input id="smg-foundation-search" type="search" placeholder="'.esc_attr__('Search modules…','smg-site-suite').'"></div>';
        echo '<div class="smg-foundation-layout"><nav class="smg-foundation-sidebar"><button class="is-active" data-category="all">'.esc_html__('All Modules','smg-site-suite').'</button>';
        foreach($categories as $slug=>$label)printf('<button data-category="%1$s">%2$s</button>',esc_attr($slug),esc_html($label));
        echo '</nav><main><div class="smg-foundation-toolbar"><div><button class="is-active" data-status="all">'.esc_html__('All','smg-site-suite').'</button><button data-status="active">'.esc_html__('Active','smg-site-suite').'</button><button data-status="inactive">'.esc_html__('Inactive','smg-site-suite').'</button></div></div><section class="smg-foundation-grid">';
        foreach($modules as $module){
            $status=$this->manager->status($module->slug());$active=$status['active'];$available=$status['available'];$search=strtolower($module->name().' '.$module->description().' '.implode(' ',$module->tags()));
            printf('<article class="smg-foundation-card" data-category="%1$s" data-status="%2$s" data-search="%3$s">',esc_attr($module->category()),$active?'active':'inactive',esc_attr($search));
            echo '<div><span class="smg-foundation-category">'.esc_html($categories[$module->category()]??$module->category()).'</span><h2>'.esc_html($module->name()).'</h2><p>'.esc_html($module->description()).'</p>';
            if(!$available)echo '<p class="smg-foundation-dependency">'.esc_html__('Unavailable: ','smg-site-suite').esc_html(implode(', ',$status['missing'])).'</p>';
            if($active&&$module->hasSettings()){$url=add_query_arg(['page'=>$this->config['menu_slug'],'module_settings'=>$module->slug()],admin_url('admin.php'));echo '<a class="button button-small" href="'.esc_url($url).'">'.esc_html__('Settings','smg-site-suite').'</a>';}
            echo '</div><label class="smg-foundation-switch"><input class="smg-foundation-toggle" type="checkbox" value="'.esc_attr($module->slug()).'" '.checked($active,true,false).' '.disabled($available,false,false).'><span></span></label></article>';
        }
        echo '</section></main></div></div>';
    }
    public function toggle():void{
        check_ajax_referer($this->ajaxAction,'nonce');if(!current_user_can((string)$this->config['capability']))wp_send_json_error(['message'=>__('Insufficient permissions.','smg-site-suite')],403);
        $slug=isset($_POST['module'])?sanitize_key(wp_unslash($_POST['module'])):'';$enabled=isset($_POST['enabled'])&&sanitize_text_field(wp_unslash($_POST['enabled']))==='true';
        try{$enabled?$this->manager->activate($slug):$this->manager->deactivate($slug);wp_send_json_success(['active'=>$this->manager->state()->active()]);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()],400);}
    }
    private function renderSettings(string $slug):void{
        try{$module=$this->manager->settingsModule($slug);}catch(Throwable $e){wp_die(esc_html($e->getMessage()));}
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['smg_foundation_save_settings'])){check_admin_referer('smg_foundation_settings_'.$slug);$input=isset($_POST['settings'])&&is_array($_POST['settings'])?wp_unslash($_POST['settings']):[];$module->saveSettings($input);echo '<div class="notice notice-success is-dismissible"><p>'.esc_html__('Settings saved.','smg-site-suite').'</p></div>';}
        $definition=$this->manager->registry()->get($slug);$back=add_query_arg('page',$this->config['menu_slug'],admin_url('admin.php'));
        echo '<div class="wrap smg-foundation-settings"><p><a href="'.esc_url($back).'">&larr; '.esc_html__('Back to modules','smg-site-suite').'</a></p><h1>'.esc_html($definition?$definition->name():$slug).'</h1><form method="post">';
        wp_nonce_field('smg_foundation_settings_'.$slug);(new SettingsRenderer())->render($module->settingsSchema(),$module->settings());submit_button(__('Save Settings','smg-site-suite'),'primary','smg_foundation_save_settings');echo '</form></div>';
    }
}
