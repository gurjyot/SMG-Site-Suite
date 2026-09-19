<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class SiteHealthExtensions implements ModuleInterface {
    public function register():void{add_filter('site_status_tests',[$this,'tests']);}

    public function tests(array $tests):array{
        $tests['direct']['smg_https']=['label'=>__('HTTPS configuration','smg-site-suite'),'test'=>[$this,'https']];
        $tests['direct']['smg_debug']=['label'=>__('Production debug configuration','smg-site-suite'),'test'=>[$this,'debug']];
        $tests['direct']['smg_search_visibility']=['label'=>__('Search engine visibility','smg-site-suite'),'test'=>[$this,'visibility']];
        $tests['direct']['smg_cron']=['label'=>__('WordPress cron configuration','smg-site-suite'),'test'=>[$this,'cron']];
        return $tests;
    }

    public function https():array{
        $ok=is_ssl()&&str_starts_with(home_url('/'),'https://')&&str_starts_with(site_url('/'),'https://');
        return $this->result($ok?__('Site URLs use HTTPS.','smg-site-suite'):__('One or more site URLs are not using HTTPS.','smg-site-suite'),$ok?'good':'recommended','smg_https');
    }

    public function debug():array{
        $enabled=defined('WP_DEBUG')&&WP_DEBUG;
        return $this->result($enabled?__('WP_DEBUG is enabled. Review this on production sites.','smg-site-suite'):__('WP_DEBUG is disabled.','smg-site-suite'),$enabled?'recommended':'good','smg_debug');
    }

    public function visibility():array{
        $blocked=(string)get_option('blog_public')==='0';
        return $this->result($blocked?__('Search engines are discouraged from indexing this site.','smg-site-suite'):__('Search engine visibility is enabled.','smg-site-suite'),$blocked?'recommended':'good','smg_visibility');
    }

    public function cron():array{
        $disabled=defined('DISABLE_WP_CRON')&&DISABLE_WP_CRON;
        return $this->result($disabled?__('WP-Cron is disabled. Confirm a real server cron is configured.','smg-site-suite'):__('WP-Cron is enabled.','smg-site-suite'),$disabled?'recommended':'good','smg_cron');
    }

    private function result(string $description,string $status,string $test):array{
        return ['label'=>$description,'status'=>$status,'badge'=>['label'=>'SMG Site Suite','color'=>'blue'],'description'=>'<p>'.esc_html($description).'</p>','actions'=>'','test'=>$test];
    }
}
