<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class FooterTimezone implements ModuleInterface {
    public function register():void{add_filter('update_footer',[$this,'footer'],90);}
    public function footer(string $text):string{
        $now=wp_date(get_option('date_format').' '.get_option('time_format'));
        $tz=wp_timezone_string();
        return esc_html($now.($tz!==''?' · '.$tz:'')).' &nbsp; '.$text;
    }
}
