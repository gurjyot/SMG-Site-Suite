<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class QuantityRules implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_quantity_rules';

    public function register():void{
        add_filter('woocommerce_quantity_input_args',[$this,'inputArgs'],20,2);
        add_filter('woocommerce_add_to_cart_validation',[$this,'validate'],20,5);
        add_filter('woocommerce_update_cart_validation',[$this,'validateCart'],20,4);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'minimum','type'=>'number','label'=>__('Minimum quantity','smg-site-suite'),'default'=>1],
            ['key'=>'maximum','type'=>'number','label'=>__('Maximum quantity','smg-site-suite'),'default'=>0,'description'=>__('Use 0 for no maximum.','smg-site-suite')],
            ['key'=>'step','type'=>'number','label'=>__('Quantity step','smg-site-suite'),'default'=>1],
        ];
    }

    public function settings():array{
        $value=get_option(self::OPTION,['minimum'=>1,'maximum'=>0,'step'=>1]);
        return is_array($value)?$value:[];
    }

    public function saveSettings(array $input):void{
        $min=max(1,absint($input['minimum']??1));
        $max=max(0,absint($input['maximum']??0));
        if($max>0&&$max<$min)$max=$min;
        $step=max(1,absint($input['step']??1));
        update_option(self::OPTION,['minimum'=>$min,'maximum'=>$max,'step'=>$step],false);
    }

    public function inputArgs(array $args,\WC_Product $product):array{
        $s=$this->settings();
        $args['min_value']=(int)($s['minimum']??1);
        if((int)($s['maximum']??0)>0)$args['max_value']=(int)$s['maximum'];
        $args['step']=(int)($s['step']??1);
        return $args;
    }

    public function validate(bool $passed,int $productId,int $quantity,int $variationId=0,array $variations=[]):bool{
        return $this->check($passed,$quantity);
    }

    public function validateCart(bool $passed,string $cartItemKey,array $values,int $quantity):bool{
        return $this->check($passed,$quantity);
    }

    private function check(bool $passed,int $quantity):bool{
        if(!$passed)return false;
        $s=$this->settings();$min=(int)($s['minimum']??1);$max=(int)($s['maximum']??0);$step=(int)($s['step']??1);
        if($quantity<$min){wc_add_notice(sprintf(__('Minimum quantity is %d.','smg-site-suite'),$min),'error');return false;}
        if($max>0&&$quantity>$max){wc_add_notice(sprintf(__('Maximum quantity is %d.','smg-site-suite'),$max),'error');return false;}
        if($step>1&&(($quantity-$min)%$step)!==0){wc_add_notice(sprintf(__('Quantity must increase in steps of %d.','smg-site-suite'),$step),'error');return false;}
        return true;
    }
}
