<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class CustomerLifetimeOrders implements ModuleInterface {
    public function register():void{
        add_filter('manage_users_columns',[$this,'column']);
        add_filter('manage_users_custom_column',[$this,'value'],20,3);
    }

    public function column(array $columns):array{$columns['smg_wc_orders']=__('Orders','smg-site-suite');$columns['smg_wc_spend']=__('Lifetime Spend','smg-site-suite');return $columns;}

    public function value(string $value,string $column,int $userId):string{
        if($column==='smg_wc_orders')return (string)wc_get_customer_order_count($userId);
        if($column==='smg_wc_spend')return wp_strip_all_tags(wc_price((float)wc_get_customer_total_spent($userId)));
        return $value;
    }
}
