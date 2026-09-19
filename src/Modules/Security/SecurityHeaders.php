<?php
namespace SMG\SiteSuite\Modules\Security;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class SecurityHeaders implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_security_headers';

    public function register():void{
        add_filter('wp_headers',[$this,'headers'],99);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'nosniff','type'=>'checkbox','label'=>__('X-Content-Type-Options: nosniff','smg-site-suite'),'default'=>true],
            ['key'=>'referrer','type'=>'select','label'=>__('Referrer-Policy','smg-site-suite'),'default'=>'strict-origin-when-cross-origin','options'=>[
                'strict-origin-when-cross-origin'=>'strict-origin-when-cross-origin',
                'no-referrer'=>'no-referrer',
                'same-origin'=>'same-origin',
                'strict-origin'=>'strict-origin',
            ]],
            ['key'=>'frame','type'=>'select','label'=>__('X-Frame-Options','smg-site-suite'),'default'=>'SAMEORIGIN','options'=>[
                'SAMEORIGIN'=>'SAMEORIGIN',
                'DENY'=>'DENY',
                'off'=>__('Off','smg-site-suite'),
            ]],
            ['key'=>'permissions','type'=>'text','label'=>__('Permissions-Policy','smg-site-suite'),'default'=>'camera=(), microphone=(), geolocation=()'],
        ];
    }

    public function settings():array{
        $v=get_option(self::OPTION,[
            'nosniff'=>true,
            'referrer'=>'strict-origin-when-cross-origin',
            'frame'=>'SAMEORIGIN',
            'permissions'=>'camera=(), microphone=(), geolocation=()',
        ]);
        return is_array($v)?$v:[];
    }

    public function saveSettings(array $input):void{
        $ref=(string)($input['referrer']??'strict-origin-when-cross-origin');
        if(!in_array($ref,['strict-origin-when-cross-origin','no-referrer','same-origin','strict-origin'],true))$ref='strict-origin-when-cross-origin';
        $frame=(string)($input['frame']??'SAMEORIGIN');
        if(!in_array($frame,['SAMEORIGIN','DENY','off'],true))$frame='SAMEORIGIN';
        update_option(self::OPTION,[
            'nosniff'=>!empty($input['nosniff']),
            'referrer'=>$ref,
            'frame'=>$frame,
            'permissions'=>sanitize_text_field((string)($input['permissions']??'')),
        ],false);
    }

    public function headers(array $headers):array{
        $s=$this->settings();
        if(!empty($s['nosniff']))$headers['X-Content-Type-Options']='nosniff';
        if(!empty($s['referrer']))$headers['Referrer-Policy']=(string)$s['referrer'];
        if(!empty($s['permissions']))$headers['Permissions-Policy']=(string)$s['permissions'];
        if(($s['frame']??'off')!=='off')$headers['X-Frame-Options']=(string)$s['frame'];
        return $headers;
    }
}
