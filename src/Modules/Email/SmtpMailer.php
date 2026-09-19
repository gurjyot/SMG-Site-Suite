<?php
namespace SMG\SiteSuite\Modules\Email;

use PHPMailer\PHPMailer\PHPMailer;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class SmtpMailer implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_smtp';

    public function register():void{add_action('phpmailer_init',[$this,'configure']);}

    public function settingsSchema():array{
        return [
            ['key'=>'host','type'=>'text','label'=>__('SMTP host','smg-site-suite'),'default'=>''],
            ['key'=>'port','type'=>'number','label'=>__('SMTP port','smg-site-suite'),'default'=>587],
            ['key'=>'encryption','type'=>'select','label'=>__('Encryption','smg-site-suite'),'default'=>'tls','options'=>['tls'=>'TLS','ssl'=>'SSL','none'=>__('None','smg-site-suite')]],
            ['key'=>'username','type'=>'text','label'=>__('Username','smg-site-suite'),'default'=>''],
            ['key'=>'password','type'=>'text','label'=>__('Password','smg-site-suite'),'default'=>''],
            ['key'=>'auth','type'=>'checkbox','label'=>__('Use SMTP authentication','smg-site-suite'),'default'=>true],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $enc=(string)($input['encryption']??'tls');if(!in_array($enc,['tls','ssl','none'],true))$enc='tls';
        update_option(self::OPTION,[
            'host'=>sanitize_text_field((string)($input['host']??'')),
            'port'=>max(1,min(65535,absint($input['port']??587))),
            'encryption'=>$enc,
            'username'=>sanitize_text_field((string)($input['username']??'')),
            'password'=>(string)($input['password']??''),
            'auth'=>!empty($input['auth']),
        ],false);
    }

    public function configure(PHPMailer $mailer):void{
        $s=$this->settings();if(empty($s['host']))return;
        $mailer->isSMTP();$mailer->Host=(string)$s['host'];$mailer->Port=(int)($s['port']??587);$mailer->SMTPAuth=!empty($s['auth']);
        $mailer->Username=(string)($s['username']??'');$mailer->Password=(string)($s['password']??'');
        if(($s['encryption']??'tls')==='none')$mailer->SMTPSecure='';
        else $mailer->SMTPSecure=(string)$s['encryption'];
    }
}
