<?php
namespace SMG\WPFoundation\Modules;
final class ContextChecker {
    public function allows(ModuleDefinition $module):bool{$contexts=$module->contexts();return in_array('all',$contexts,true)||in_array($this->current(),$contexts,true);}
    public function current():string{
        if(defined('WP_CLI')&&WP_CLI)return 'cli'; if(wp_doing_cron())return 'cron'; if(defined('REST_REQUEST')&&REST_REQUEST)return 'rest';
        if(wp_doing_ajax())return 'ajax'; if(is_admin())return 'admin'; return 'frontend';
    }
}
