<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use DateTimeImmutable;
use SMG\WPFoundation\Contracts\ActivatableModuleInterface;
use SMG\WPFoundation\Contracts\DeactivatableModuleInterface;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class FomoSalesNotifications implements SettingsModuleInterface, ActivatableModuleInterface, DeactivatableModuleInterface {
    private const OPTION='smg_site_suite_fomo_settings';
    private const SNAPSHOT='smg_site_suite_fomo_snapshot';
    private const EVENT='smg_site_suite_fomo_refresh';

    public function register():void{
        add_action(self::EVENT,[$this,'refreshSnapshot']);
        add_action('wp_enqueue_scripts',[$this,'assets']);
    }

    public function activate():void{
        if(!wp_next_scheduled(self::EVENT)){
            wp_schedule_event($this->nextNightTimestamp(),'daily',self::EVENT);
        }
    }

    public function deactivate():void{
        $timestamp=wp_next_scheduled(self::EVENT);
        while($timestamp){
            wp_unschedule_event($timestamp,self::EVENT);
            $timestamp=wp_next_scheduled(self::EVENT);
        }
    }

    public function settingsSchema():array{
        return [
            ['key'=>'delay','type'=>'number','label'=>__('First notification delay (seconds)','smg-site-suite'),'default'=>5],
            ['key'=>'interval','type'=>'number','label'=>__('Time between notifications (seconds)','smg-site-suite'),'default'=>8],
            ['key'=>'duration','type'=>'number','label'=>__('Notification visible time (seconds)','smg-site-suite'),'default'=>5],
            ['key'=>'position','type'=>'select','label'=>__('Position','smg-site-suite'),'default'=>'bottom-left','options'=>[
                'bottom-left'=>__('Bottom left','smg-site-suite'),
                'bottom-right'=>__('Bottom right','smg-site-suite')
            ]]
        ];
    }

    public function settings():array{
        $value=get_option(self::OPTION,['delay'=>5,'interval'=>8,'duration'=>5,'position'=>'bottom-left']);
        return is_array($value)?$value:[];
    }

    public function saveSettings(array $input):void{
        $position=(string)($input['position']??'bottom-left');
        if(!in_array($position,['bottom-left','bottom-right'],true))$position='bottom-left';

        update_option(self::OPTION,[
            'delay'=>max(1,min(60,absint($input['delay']??5))),
            'interval'=>max(3,min(120,absint($input['interval']??8))),
            'duration'=>max(2,min(30,absint($input['duration']??5))),
            'position'=>$position,
        ],false);
    }

    public function refreshSnapshot():void{
        $orders=wc_get_orders([
            'limit'=>20,
            'orderby'=>'date',
            'order'=>'DESC',
            'status'=>wc_get_is_paid_statuses(),
            'return'=>'objects',
        ]);

        $snapshot=[];
        foreach($orders as $order){
            if(!$order instanceof \WC_Order)continue;

            $city=trim((string)$order->get_billing_city());
            $firstName=trim((string)$order->get_billing_first_name());
            $initial=$firstName!==''?(function_exists('mb_substr')?mb_substr($firstName,0,1):substr($firstName,0,1)):'';
            $customer=$initial!==''?$initial.'***':__('Someone','smg-site-suite');

            foreach($order->get_items('line_item') as $item){
                if(!$item instanceof \WC_Order_Item_Product)continue;
                $product=$item->get_product();
                if(!$product)continue;

                $snapshot[]=[
                    'customer'=>$customer,
                    'city'=>$city,
                    'product'=>$item->get_name(),
                    'url'=>$product->is_visible()?get_permalink($product->get_id()):'',
                    'image'=>wp_get_attachment_image_url($product->get_image_id(),'thumbnail')?:wc_placeholder_img_src('thumbnail'),
                ];
                break;
            }

            if(count($snapshot)>=20)break;
        }

        update_option(self::SNAPSHOT,$snapshot,false);
        update_option(self::SNAPSHOT.'_updated',time(),false);
    }

    public function assets():void{
        if(is_admin())return;

        $snapshot=get_option(self::SNAPSHOT,[]);
        if(!is_array($snapshot)||$snapshot===[])return;

        $settings=$this->settings();

        wp_enqueue_style('smg-site-suite-fomo',SMG_SITE_SUITE_URL.'assets/fomo.css',[],SMG_SITE_SUITE_VERSION);
        wp_enqueue_script('smg-site-suite-fomo',SMG_SITE_SUITE_URL.'assets/fomo.js',[],SMG_SITE_SUITE_VERSION,true);
        wp_localize_script('smg-site-suite-fomo','smgSiteSuiteFomo',[
            'items'=>array_values($snapshot),
            'delay'=>((int)($settings['delay']??5))*1000,
            'interval'=>((int)($settings['interval']??8))*1000,
            'duration'=>((int)($settings['duration']??5))*1000,
            'position'=>(string)($settings['position']??'bottom-left'),
            'template'=>__('{{customer}}{{city}} purchased {{product}}','smg-site-suite'),
        ]);
    }

    private function nextNightTimestamp():int{
        $timezone=wp_timezone();
        $now=new DateTimeImmutable('now',$timezone);
        $next=$now->setTime(2,30);
        if($next<=$now)$next=$next->modify('+1 day');
        return $next->getTimestamp();
    }
}
