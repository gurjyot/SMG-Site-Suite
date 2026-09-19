<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CustomDashboardPage implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_custom_dashboard';

    public function register():void{
        add_action('wp_dashboard_setup',[$this,'dashboard'],9999);
        add_action('admin_head-index.php',[$this,'styles']);
        add_filter('show_admin_bar',[$this,'hideEmbeddedAdminBar']);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'page_id','type'=>'page_select','label'=>__('Dashboard page','smg-site-suite'),'description'=>__('Choose any published WordPress page. It can be designed with Bricks, Elementor, Beaver Builder, Gutenberg, or another page builder.','smg-site-suite')],
            ['key'=>'height','type'=>'number','label'=>__('Dashboard height (px)','smg-site-suite'),'default'=>1000],
            ['key'=>'replace','type'=>'checkbox','label'=>__('Replace all default dashboard widgets','smg-site-suite'),'default'=>true],
        ];
    }

    public function settings():array{
        $v=get_option(self::OPTION,['page_id'=>0,'height'=>1000,'replace'=>true]);
        return is_array($v)?$v:[];
    }

    public function saveSettings(array $input):void{
        $pageId=absint($input['page_id']??0);
        if($pageId>0&&get_post_type($pageId)!=='page')$pageId=0;
        update_option(self::OPTION,[
            'page_id'=>$pageId,
            'height'=>max(400,min(4000,absint($input['height']??1000))),
            'replace'=>!empty($input['replace']),
        ],false);
    }

    public function dashboard():void{
        $settings=$this->settings();
        $pageId=(int)($settings['page_id']??0);
        if($pageId<=0||get_post_status($pageId)!=='publish')return;

        if(!empty($settings['replace'])){
            global $wp_meta_boxes;
            $wp_meta_boxes['dashboard']=[];
        }

        wp_add_dashboard_widget(
            'smg_site_suite_custom_dashboard',
            get_the_title($pageId),
            [$this,'render']
        );
    }

    public function render():void{
        $settings=$this->settings();
        $pageId=(int)($settings['page_id']??0);
        if($pageId<=0)return;
        $url=add_query_arg('smg_site_suite_dashboard_embed','1',get_permalink($pageId));
        echo '<iframe class="smg-site-suite-dashboard-frame" src="'.esc_url($url).'" title="'.esc_attr(get_the_title($pageId)).'" loading="eager"></iframe>';
    }

    public function styles():void{
        $settings=$this->settings();$height=(int)($settings['height']??1000);
        if((int)($settings['page_id']??0)<=0)return;
        echo '<style>
        #dashboard-widgets .postbox-container{width:100%!important}
        #dashboard-widgets #smg_site_suite_custom_dashboard{border:0;box-shadow:none;background:transparent}
        #dashboard-widgets #smg_site_suite_custom_dashboard .postbox-header{display:none}
        #dashboard-widgets #smg_site_suite_custom_dashboard .inside{margin:0;padding:0}
        .smg-site-suite-dashboard-frame{display:block;width:100%;height:'.esc_attr((string)$height).'px;border:0;background:#fff}
        </style>';
    }

    public function hideEmbeddedAdminBar(bool $show):bool{
        if(isset($_GET['smg_site_suite_dashboard_embed'])&&'1'===sanitize_text_field(wp_unslash($_GET['smg_site_suite_dashboard_embed'])))return false;
        return $show;
    }
}
