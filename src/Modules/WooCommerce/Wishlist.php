<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ActivatableModuleInterface;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class Wishlist implements SettingsModuleInterface, ActivatableModuleInterface {
    private const OPTION='smg_site_suite_wishlist_settings';
    private const PAGE_OPTION='smg_site_suite_wishlist_page_id';
    private const USER_META='_smg_site_suite_wishlist';
    private const SESSION_KEY='smg_site_suite_wishlist';

    public function register():void{
        add_action('wp_enqueue_scripts',[$this,'assets']);
        add_action('woocommerce_after_add_to_cart_button',[$this,'singleButton'],25);
        add_action('woocommerce_after_shop_loop_item',[$this,'loopButton'],20);
        add_shortcode('smg_wishlist',[$this,'shortcode']);
        add_shortcode('smg_wishlist_count',[$this,'countShortcode']);
        add_shortcode('smg_wishlist_link',[$this,'linkShortcode']);
        add_action('admin_post_smg_site_suite_wishlist_move_all',[$this,'moveAllToCart']);
        add_action('admin_post_nopriv_smg_site_suite_wishlist_move_all',[$this,'moveAllToCart']);
        add_action('wp_ajax_smg_site_suite_wishlist_toggle',[$this,'ajaxToggle']);
        add_action('wp_ajax_nopriv_smg_site_suite_wishlist_toggle',[$this,'ajaxToggle']);
        add_action('woocommerce_init',[$this,'mergeGuestIntoAccount']);
        add_action('woocommerce_thankyou',[$this,'removePurchased'],20);
    }

    public function activate():void{
        $pageId=(int)get_option(self::PAGE_OPTION,0);
        if($pageId>0&&get_post_status($pageId))return;

        $existing=get_page_by_path('wishlist');
        if($existing instanceof \WP_Post){
            update_option(self::PAGE_OPTION,$existing->ID,false);
            return;
        }

        $pageId=wp_insert_post([
            'post_title'=>__('Wishlist','smg-site-suite'),
            'post_name'=>'wishlist',
            'post_content'=>'[smg_wishlist]',
            'post_status'=>'publish',
            'post_type'=>'page',
        ],true);

        if(!is_wp_error($pageId))update_option(self::PAGE_OPTION,(int)$pageId,false);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'button_text','type'=>'text','label'=>__('Button text','smg-site-suite'),'default'=>__('Wishlist','smg-site-suite')],
            ['key'=>'remove_text','type'=>'text','label'=>__('Remove text','smg-site-suite'),'default'=>__('Remove from wishlist','smg-site-suite')],
            ['key'=>'show_loop','type'=>'checkbox','label'=>__('Show button on product archives','smg-site-suite'),'default'=>true],
            ['key'=>'auto_remove','type'=>'checkbox','label'=>__('Remove purchased products from wishlist','smg-site-suite'),'default'=>true],
        ];
    }

    public function settings():array{
        $value=get_option(self::OPTION,[
            'button_text'=>__('Wishlist','smg-site-suite'),
            'remove_text'=>__('Remove from wishlist','smg-site-suite'),
            'show_loop'=>true,
            'auto_remove'=>true,
        ]);
        return is_array($value)?$value:[];
    }

    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'button_text'=>sanitize_text_field((string)($input['button_text']??__('Wishlist','smg-site-suite'))),
            'remove_text'=>sanitize_text_field((string)($input['remove_text']??__('Remove from wishlist','smg-site-suite'))),
            'show_loop'=>!empty($input['show_loop']),
            'auto_remove'=>!empty($input['auto_remove']),
        ],false);
    }

    public function assets():void{
        $pageId=(int)get_option(self::PAGE_OPTION,0);
        if(!is_woocommerce()&&!is_page($pageId))return;

        wp_enqueue_style('smg-site-suite-wishlist',SMG_SITE_SUITE_URL.'assets/wishlist.css',[],SMG_SITE_SUITE_VERSION);
        wp_enqueue_script('smg-site-suite-wishlist',SMG_SITE_SUITE_URL.'assets/wishlist.js',[],SMG_SITE_SUITE_VERSION,true);
        wp_localize_script('smg-site-suite-wishlist','smgSiteSuiteWishlist',[
            'ajaxUrl'=>admin_url('admin-ajax.php'),
            'nonce'=>wp_create_nonce('smg_site_suite_wishlist'),
            'items'=>$this->items(),
            'addText'=>(string)($this->settings()['button_text']??__('Wishlist','smg-site-suite')),
            'removeText'=>(string)($this->settings()['remove_text']??__('Remove from wishlist','smg-site-suite')),
        ]);
    }

    public function singleButton():void{
        global $product;
        if($product instanceof \WC_Product)$this->button($product->get_id());
    }

    public function loopButton():void{
        if(empty($this->settings()['show_loop']))return;
        global $product;
        if($product instanceof \WC_Product)$this->button($product->get_id());
    }

    private function button(int $productId):void{
        if($productId<=0)return;
        $active=in_array($productId,$this->items(),true);
        $settings=$this->settings();
        $text=$active?($settings['remove_text']??__('Remove from wishlist','smg-site-suite')):($settings['button_text']??__('Wishlist','smg-site-suite'));
        echo '<button type="button" class="button smgss-wishlist-toggle'.($active?' is-active':'').'" data-product-id="'.esc_attr((string)$productId).'" aria-pressed="'.($active?'true':'false').'">'.esc_html((string)$text).'</button>';
    }

    public function ajaxToggle():void{
        check_ajax_referer('smg_site_suite_wishlist','nonce');

        $productId=isset($_POST['product_id'])?absint($_POST['product_id']):0;
        $product=wc_get_product($productId);
        if(!$product)wp_send_json_error(['message'=>__('Invalid product.','smg-site-suite')],400);

        $items=$this->items();
        $added=!in_array($productId,$items,true);

        if($added)$items[]=$productId;
        else $items=array_values(array_diff($items,[$productId]));

        $items=$this->normalize($items);
        $this->saveItems($items);

        $settings=$this->settings();
        wp_send_json_success([
            'added'=>$added,
            'count'=>count($items),
            'items'=>$items,
            'text'=>$added?(string)($settings['remove_text']??__('Remove from wishlist','smg-site-suite')):(string)($settings['button_text']??__('Wishlist','smg-site-suite')),
        ]);
    }

    public function countShortcode():string{
        return (string)count($this->items());
    }

    public function linkShortcode():string{
        $pageId=(int)get_option(self::PAGE_OPTION,0);
        $url=$pageId>0?get_permalink($pageId):home_url('/wishlist/');
        return '<a class="smgss-wishlist-link" href="'.esc_url($url).'">'.esc_html(sprintf(__('Wishlist (%d)','smg-site-suite'),count($this->items()))).'</a>';
    }

    public function shortcode():string{
        $shared=$this->sharedItems();
        $ids=$shared!==null?$shared:$this->items();
        $readOnly=$shared!==null;
        if($ids===[])return '<div class="smgss-wishlist-empty">'.esc_html__('Your wishlist is empty.','smg-site-suite').'</div>';

        $products=wc_get_products(['include'=>$ids,'limit'=>-1,'status'=>'publish']);
        $map=[];
        foreach($products as $product)if($product instanceof \WC_Product)$map[$product->get_id()]=$product;

        ob_start();
        $subtotal=0.0;
        echo '<div class="smgss-wishlist-summary">';
        if(!$readOnly){
            $share=$this->shareUrl($ids);
            echo '<a class="button" href="'.esc_url($share).'">'.esc_html__('Share Wishlist','smg-site-suite').'</a> ';
            echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" style="display:inline-block"><input type="hidden" name="action" value="smg_site_suite_wishlist_move_all">';
            wp_nonce_field('smg_site_suite_wishlist_move_all');
            echo '<button class="button" type="submit">'.esc_html__('Move Eligible Items to Cart','smg-site-suite').'</button></form>';
        }
        echo '</div><div class="smgss-wishlist-grid">';
        foreach($ids as $id){
            $product=$map[$id]??null;if(!$product)continue;
            echo '<article class="smgss-wishlist-item" data-product-id="'.esc_attr((string)$id).'">';
            echo '<a href="'.esc_url($product->get_permalink()).'" class="smgss-wishlist-image">'.wp_kses_post($product->get_image('woocommerce_thumbnail')).'</a>';
            echo '<div class="smgss-wishlist-info"><h3><a href="'.esc_url($product->get_permalink()).'">'.esc_html($product->get_name()).'</a></h3>';
            $subtotal+=(float)$product->get_price();
            echo '<div class="smgss-wishlist-price">'.wp_kses_post($product->get_price_html()).'</div>';
            echo '<div class="smgss-wishlist-stock">'.esc_html($product->is_in_stock()?__('In stock','smg-site-suite'):__('Out of stock','smg-site-suite')).'</div>';
            echo '<a class="button" href="'.esc_url($product->add_to_cart_url()).'">'.esc_html($product->add_to_cart_text()).'</a> ';
            if(!$readOnly)echo '<button type="button" class="button smgss-wishlist-toggle is-active" data-product-id="'.esc_attr((string)$id).'" aria-pressed="true">'.esc_html((string)($this->settings()['remove_text']??__('Remove from wishlist','smg-site-suite'))).'</button>';
            echo '</div></article>';
        }
        echo '</div>';
        echo '<div class="smgss-wishlist-subtotal"><strong>'.esc_html__('Wishlist subtotal:','smg-site-suite').'</strong> '.wp_kses_post(wc_price($subtotal)).'</div>';
        return (string)ob_get_clean();
    }

    public function moveAllToCart():void{
        check_admin_referer('smg_site_suite_wishlist_move_all');
        if(!WC()->cart)wc_load_cart();
        if(!WC()->cart)wp_die(esc_html__('Cart is unavailable.','smg-site-suite'));

        $items=$this->items();
        $remaining=[];

        foreach($items as $productId){
            $product=wc_get_product($productId);
            if(!$product||!$product->is_purchasable()||!$product->is_in_stock()||!$product->is_type('simple')){
                $remaining[]=$productId;
                continue;
            }

            $added=WC()->cart->add_to_cart($productId,1);
            if(!$added)$remaining[]=$productId;
        }

        $this->saveItems($this->normalize($remaining));
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }

    public function mergeGuestIntoAccount():void{
        if(!is_user_logged_in()||!WC()->session)return;
        $guest=$this->sessionItems();
        if($guest===[])return;

        $userId=get_current_user_id();
        $user=$this->normalize((array)get_user_meta($userId,self::USER_META,true));
        update_user_meta($userId,self::USER_META,$this->normalize(array_merge($user,$guest)));
        WC()->session->set(self::SESSION_KEY,[]);
    }

    public function removePurchased(int $orderId):void{
        if(empty($this->settings()['auto_remove']))return;
        $order=wc_get_order($orderId);if(!$order)return;

        $purchased=[];
        foreach($order->get_items('line_item') as $item){
            if(!$item instanceof \WC_Order_Item_Product)continue;
            $purchased[]=$item->get_variation_id()?:$item->get_product_id();
            $purchased[]=$item->get_product_id();
        }

        $userId=(int)$order->get_customer_id();
        if($userId>0){
            $items=$this->normalize((array)get_user_meta($userId,self::USER_META,true));
            update_user_meta($userId,self::USER_META,array_values(array_diff($items,$purchased)));
        }elseif(WC()->session){
            WC()->session->set(self::SESSION_KEY,array_values(array_diff($this->sessionItems(),$purchased)));
        }
    }

    private function items():array{
        if(is_user_logged_in()){
            return $this->normalize((array)get_user_meta(get_current_user_id(),self::USER_META,true));
        }
        return $this->sessionItems();
    }

    private function sessionItems():array{
        if(!WC()->session)return [];
        return $this->normalize((array)WC()->session->get(self::SESSION_KEY,[]));
    }

    private function saveItems(array $items):void{
        if(is_user_logged_in()){
            update_user_meta(get_current_user_id(),self::USER_META,$items);
            return;
        }

        if(WC()->session){
            if(!WC()->session->has_session())WC()->session->set_customer_session_cookie(true);
            WC()->session->set(self::SESSION_KEY,$items);
        }
    }

    private function shareUrl(array $ids):string{
        $ids=array_slice($this->normalize($ids),0,50);
        $payload=implode(',',$ids);
        $encoded=rtrim(strtr(base64_encode($payload),'+/','-_'),'=');
        $sig=hash_hmac('sha256',$encoded,wp_salt('auth'));
        $pageId=(int)get_option(self::PAGE_OPTION,0);
        $base=$pageId>0?get_permalink($pageId):home_url('/wishlist/');
        return add_query_arg(['smg_wishlist_share'=>$encoded,'sig'=>$sig],$base);
    }

    private function sharedItems():?array{
        if(empty($_GET['smg_wishlist_share'])||empty($_GET['sig']))return null;
        $encoded=sanitize_text_field(wp_unslash($_GET['smg_wishlist_share']));
        $sig=sanitize_text_field(wp_unslash($_GET['sig']));
        $expected=hash_hmac('sha256',$encoded,wp_salt('auth'));
        if(!hash_equals($expected,$sig))return [];
        $padded=strtr($encoded,'-_','+/');
        $padding=strlen($padded)%4;
        if($padding)$padded.=str_repeat('=',4-$padding);
        $decoded=base64_decode($padded,true);
        if(!is_string($decoded))return [];
        return array_slice($this->normalize(explode(',',$decoded)),0,50);
    }

    private function normalize(array $items):array{
        return array_values(array_unique(array_filter(array_map('absint',$items))));
    }
}
