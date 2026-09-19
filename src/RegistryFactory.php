<?php
namespace SMG\SiteSuite;
use SMG\WPFoundation\Modules\ModuleDefinition;use SMG\WPFoundation\Modules\ModuleRegistry;
use SMG\SiteSuite\Modules\Admin\AdminFooter;use SMG\SiteSuite\Modules\Admin\DashboardWidgets;use SMG\SiteSuite\Modules\Admin\LoginBranding;
use SMG\SiteSuite\Modules\Content\DisableComments;use SMG\SiteSuite\Modules\Content\DuplicateContent;use SMG\SiteSuite\Modules\Content\RevisionControl;
use SMG\SiteSuite\Modules\Performance\HeartbeatControl;
use SMG\SiteSuite\Modules\WooCommerce\DisableReviews;use SMG\SiteSuite\Modules\WooCommerce\PaymentMethodColumn;use SMG\SiteSuite\Modules\WooCommerce\OrderAmountRules;use SMG\SiteSuite\Modules\WooCommerce\FreeShippingOnly;
use SMG\SiteSuite\Modules\Performance\DisableEmojis;
use SMG\SiteSuite\Modules\Performance\DisableEmbeds;
use SMG\SiteSuite\Modules\Media\SafeSvgUpload;
use SMG\SiteSuite\Modules\Media\ImageSizeControl;
use SMG\SiteSuite\Modules\Security\HideWpVersion;
use SMG\SiteSuite\Modules\WooCommerce\BuyNow;
use SMG\SiteSuite\Modules\WooCommerce\CodRules;
use SMG\SiteSuite\Modules\WooCommerce\ShippingProgressBar;
use SMG\SiteSuite\Modules\WooCommerce\FomoSalesNotifications;
final class RegistryFactory {
    public static function make():ModuleRegistry{
        $r=(new ModuleRegistry())->addCategory('admin',__('Admin','smg-site-suite'))->addCategory('content',__('Content','smg-site-suite'))->addCategory('media',__('Media','smg-site-suite'))->addCategory('performance',__('Performance','smg-site-suite'))->addCategory('security',__('Security','smg-site-suite'))->addCategory('utilities',__('Utilities','smg-site-suite'))->addCategory('woocommerce',__('WooCommerce','smg-site-suite'));
        $defs=[
            ['disable-comments','Disable Comments','Disable comments, pingbacks, trackbacks, and comment admin surfaces.','content',DisableComments::class,['comments','spam'],['all'],'low',false,[]],
            ['duplicate-content','Duplicate Content','Duplicate posts, pages, and public custom post types from the list screen.','content',DuplicateContent::class,['duplicate','clone'],['admin'],'medium',false,[]],
            ['login-branding','Login Branding','Customize the WordPress login logo and background.','admin',LoginBranding::class,['login','branding'],['admin'],'low',true,[]],
            ['admin-footer','Admin Footer','Customize the WordPress admin footer text and version visibility.','admin',AdminFooter::class,['footer','admin'],['admin'],'low',true,[]],
            ['dashboard-widgets','Dashboard Widgets','Hide selected default WordPress dashboard widgets.','admin',DashboardWidgets::class,['dashboard','widgets'],['admin'],'low',true,[]],
            ['revision-control','Revision Control','Limit or disable post revisions without editing wp-config.php.','content',RevisionControl::class,['revisions','content'],['all'],'low',true,[]],
            ['heartbeat-control','Heartbeat Control','Adjust WordPress Heartbeat frequency to reduce unnecessary admin requests.','performance',HeartbeatControl::class,['heartbeat','performance'],['admin','ajax'],'low',true,[]],
            ['safe-svg-upload','Safe SVG Upload','Allow SVG uploads only after conservative XML sanitization.','media',SafeSvgUpload::class,['svg','media','upload'],['admin','ajax','rest'],'medium',false,['classes'=>['DOMDocument']]],
            ['image-size-control','Image Size Control','Disable selected WordPress-generated image sizes for future uploads.','media',ImageSizeControl::class,['images','media','sizes'],['admin','ajax','rest'],'low',true,[]],
            ['disable-emojis','Disable Emoji Assets','Remove WordPress emoji scripts, styles, and related resource hints.','performance',DisableEmojis::class,['emoji','performance','assets'],['all'],'low',false,[]],
            ['disable-embeds','Disable WordPress Embeds','Remove WordPress oEmbed discovery, embed scripts, and oEmbed REST endpoints.','performance',DisableEmbeds::class,['embeds','oembed','performance'],['all'],'low',false,[]],
            ['hide-wp-version','Hide WordPress Version','Remove WordPress generator output and version query strings from enqueued assets.','security',HideWpVersion::class,['version','security','generator'],['all'],'low',false,[]],
            ['disable-woo-reviews','Disable Product Reviews','Disable WooCommerce product reviews without affecting normal post comments.','woocommerce',DisableReviews::class,['woocommerce','reviews'],['all'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['payment-method-column','Payment Method Column','Show the payment method directly in WooCommerce order lists, including HPOS.','woocommerce',PaymentMethodColumn::class,['woocommerce','orders','hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['order-amount-rules','Minimum / Maximum Order Amount','Set optional minimum and maximum cart totals for checkout.','woocommerce',OrderAmountRules::class,['woocommerce','checkout','minimum','maximum'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['free-shipping-only','Free Shipping Method Control','Hide other shipping methods when free shipping is available.','woocommerce',FreeShippingOnly::class,['woocommerce','shipping','free shipping'],['frontend','ajax','rest'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['buy-now','Buy Now Button','Add a Buy Now button that sends successful add-to-cart actions directly to checkout.','woocommerce',BuyNow::class,['woocommerce','conversion','checkout'],['frontend','ajax'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['cod-rules','COD Amount Rules','Show COD only when the cart total is within configured minimum and maximum amounts.','woocommerce',CodRules::class,['woocommerce','cod','payment'],['frontend','ajax','rest'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['shipping-progress','Free Shipping Progress Bar','Show customers how much more they need to spend to reach a configured free-shipping threshold.','woocommerce',ShippingProgressBar::class,['woocommerce','shipping','progress'],['frontend','ajax'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['fomo-sales-notifications','FOMO Sales Notifications','Cache up to 20 recent paid orders once daily and show lightweight randomized purchase notifications.','woocommerce',FomoSalesNotifications::class,['woocommerce','fomo','sales','notifications'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
        ];
        foreach($defs as [$slug,$name,$description,$category,$class,$tags,$contexts,$risk,$settings,$dependencies]){
            $r->register(new ModuleDefinition(['slug'=>$slug,'name'=>__($name,'smg-site-suite'),'description'=>__($description,'smg-site-suite'),'category'=>$category,'class'=>$class,'tags'=>$tags,'contexts'=>$contexts,'risk'=>$risk,'has_settings'=>$settings,'dependencies'=>$dependencies]));
        }
        return $r;
    }
}
