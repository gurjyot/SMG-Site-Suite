<?php
namespace SMG\SiteSuite;
use SMG\SiteSuite\Admin\ConfigurationsPage;use SMG\SiteSuite\Admin\CompatibilityGuard;
use SMG\WPFoundation\Admin\ModuleBrowser;use SMG\WPFoundation\Modules\DependencyChecker;use SMG\WPFoundation\Modules\ModuleInstaller;use SMG\WPFoundation\Modules\ModuleLoader;use SMG\WPFoundation\Modules\ModuleManager;use SMG\WPFoundation\Modules\ModuleStateStore;
final class Plugin {
    private static ?self $instance=null;
    public static function instance():self{return self::$instance??=new self();}
    public function boot():void{
        $registry=RegistryFactory::make();$state=new ModuleStateStore('smg_site_suite_active_modules');$deps=new DependencyChecker();
        (new ModuleInstaller($registry,$state,'smg_site_suite_installed'))->initializeDefaultsOnce();
        $manager=new ModuleManager($registry,$state,$deps);$loader=new ModuleLoader($registry,$state,$deps);
        add_action('plugins_loaded',[$loader,'loadActive'],5);
        if(is_admin()){
            (new CompatibilityGuard($registry,$state))->boot();
            (new ConfigurationsPage($manager))->boot();
            (new ModuleBrowser($manager,['page_title'=>__('SMG Site Suite','smg-site-suite'),'menu_title'=>__('Site Suite','smg-site-suite'),'menu_slug'=>'smg-site-suite','asset_url'=>SMG_SITE_SUITE_URL.'vendor/smg-wp-foundation/assets','version'=>SMG_SITE_SUITE_VERSION]))->boot();
        }
    }
}
