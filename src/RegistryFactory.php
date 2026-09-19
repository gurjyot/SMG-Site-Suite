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
use SMG\SiteSuite\Modules\Performance\DisableDashiconsFrontend;
use SMG\SiteSuite\Modules\Security\DisableFileEditing;
use SMG\SiteSuite\Modules\Admin\ShowIds;
use SMG\SiteSuite\Modules\Admin\ActivePluginsFirst;
use SMG\SiteSuite\Modules\Admin\FeaturedImageColumn;
use SMG\SiteSuite\Modules\Admin\DisableAdminBarFrontend;
use SMG\SiteSuite\Modules\Admin\HideAdminNotices;
use SMG\SiteSuite\Modules\Admin\FooterTimezone;
use SMG\SiteSuite\Modules\Utilities\SearchVisibilityStatus;
use SMG\SiteSuite\Modules\Users\LastLoginColumn;
use SMG\SiteSuite\Modules\Users\RegistrationDateColumn;
use SMG\SiteSuite\Modules\Performance\CleanWpHead;
use SMG\SiteSuite\Modules\Security\DisableXmlRpc;
use SMG\SiteSuite\Modules\Security\DisableApplicationPasswords;
use SMG\SiteSuite\Modules\WooCommerce\OrderPhoneColumn;
use SMG\SiteSuite\Modules\Performance\DisableFeeds;
use SMG\SiteSuite\Modules\Security\DisableAuthorArchives;
use SMG\SiteSuite\Modules\Content\ExternalLinksNewTab;
use SMG\SiteSuite\Modules\Utilities\CustomExcerptLength;
use SMG\SiteSuite\Modules\Content\CustomFrontendCss;
use SMG\SiteSuite\Modules\Admin\CustomAdminCss;
use SMG\SiteSuite\Modules\Content\AutoPublishMissedSchedules;
use SMG\SiteSuite\Modules\Media\DisableBigImageScaling;
use SMG\SiteSuite\Modules\Content\DisableSelfPingbacks;
use SMG\SiteSuite\Modules\Content\RemoveCommentWebsiteField;
use SMG\SiteSuite\Modules\Email\EmailSenderIdentity;
use SMG\SiteSuite\Modules\Admin\SystemSummary;
use SMG\SiteSuite\Modules\WooCommerce\Wishlist;
final class RegistryFactory {
    public static function make():ModuleRegistry{
        $r=(new ModuleRegistry())->addCategory('admin',__('Admin','smg-site-suite'))->addCategory('content',__('Content','smg-site-suite'))->addCategory('media',__('Media','smg-site-suite'))->addCategory('performance',__('Performance','smg-site-suite'))->addCategory('security',__('Security','smg-site-suite'))->addCategory('utilities',__('Utilities','smg-site-suite'))->addCategory('users',__('Users','smg-site-suite'))->addCategory('email',__('Email','smg-site-suite'))->addCategory('woocommerce',__('WooCommerce','smg-site-suite'));
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
            ['hide-wp-version','Hide WordPress Version','Remove WordPress generator output and core version query strings without breaking plugin/theme cache busting.','security',HideWpVersion::class,['version','security','generator'],['all'],'low',false,[]],
            ['disable-xml-rpc','Disable XML-RPC','Disable XML-RPC and remove pingback discovery headers.','security',DisableXmlRpc::class,['xmlrpc','pingback','security'],['all'],'low',false,[]],
            ['disable-application-passwords','Disable Application Passwords','Disable WordPress Application Password authentication when it is not needed.','security',DisableApplicationPasswords::class,['application passwords','security','auth'],['all'],'low',false,[]],
            ['clean-wp-head','Clean WordPress Head','Remove legacy RSD, WLW, shortlink, and adjacent-post discovery tags.','performance',CleanWpHead::class,['head','cleanup','performance'],['frontend'],'low',false,[]],
            ['disable-feeds','Disable Feeds','Disable WordPress RSS/Atom feeds and remove feed discovery links.','performance',DisableFeeds::class,['feeds','rss','performance'],['all'],'low',false,[]],
            ['disable-author-archives','Disable Author Archives','Redirect author archive URLs to the homepage and remove author archive links.','security',DisableAuthorArchives::class,['authors','security','archives'],['frontend'],'medium',false,[]],
            ['external-links-new-tab','External Links in New Tab','Open external HTTP/HTTPS links in a new tab with noopener protection.','content',ExternalLinksNewTab::class,['external links','content','new tab'],['frontend'],'low',false,[]],
            ['custom-excerpt-length','Custom Excerpt Length','Control the word length of automatically generated WordPress excerpts.','utilities',CustomExcerptLength::class,['excerpt','content','length'],['all'],'low',true,[]],
            ['custom-frontend-css','Custom Frontend CSS','Add lightweight custom CSS to the public site without editing theme files.','content',CustomFrontendCss::class,['css','frontend','design'],['frontend'],'medium',true,[]],
            ['custom-admin-css','Custom Admin CSS','Add lightweight custom CSS to wp-admin without editing plugin or theme files.','admin',CustomAdminCss::class,['css','admin','design'],['admin'],'medium',true,[]],
            ['auto-publish-missed-schedules','Recover Missed Scheduled Posts','Publish a small batch of overdue scheduled posts when normal WP-Cron misses them.','content',AutoPublishMissedSchedules::class,['schedule','publishing','cron'],['frontend'],'low',false,[]],
            ['disable-big-image-scaling','Disable Big Image Scaling','Stop WordPress from automatically scaling down very large uploaded images.','media',DisableBigImageScaling::class,['images','media','scaling'],['admin','ajax','rest'],'low',false,[]],
            ['disable-self-pingbacks','Disable Self Pingbacks','Prevent WordPress from pinging your own site when linking internally.','content',DisableSelfPingbacks::class,['pingback','content','links'],['all'],'low',false,[]],
            ['remove-comment-website-field','Remove Comment Website Field','Remove the website URL field from the default WordPress comment form.','content',RemoveCommentWebsiteField::class,['comments','spam','form'],['frontend'],'low',false,[]],
            ['email-sender-identity','Email Sender Identity','Set a custom default From name and From email for WordPress mail.','email',EmailSenderIdentity::class,['email','sender','mail'],['all'],'low',true,[]],
            ['system-summary','System Summary','Add a read-only Site Suite system summary page for WordPress, PHP, database, theme, memory, timezone, and debug status.','admin',SystemSummary::class,['system','diagnostics','admin'],['admin'],'low',false,[]],
            ['disable-dashicons-frontend','Disable Dashicons for Guests','Stop loading Dashicons on the public frontend for logged-out visitors.','performance',DisableDashiconsFrontend::class,['dashicons','performance','assets'],['frontend'],'low',false,[]],
            ['disable-file-editing','Disable Theme / Plugin File Editors','Remove access to the built-in WordPress theme and plugin code editors.','security',DisableFileEditing::class,['file editor','security','admin'],['admin'],'low',false,[]],
            ['show-ids','Show IDs','Add ID columns to WordPress post type and taxonomy list tables.','admin',ShowIds::class,['ids','admin','columns'],['admin'],'low',false,[]],
            ['active-plugins-first','Active Plugins First','Sort active plugins to the top of the Plugins screen.','admin',ActivePluginsFirst::class,['plugins','admin','productivity'],['admin'],'low',false,[]],
            ['featured-image-column','Featured Image Column','Show featured-image thumbnails in supported content list tables.','admin',FeaturedImageColumn::class,['featured image','admin','columns'],['admin'],'low',false,[]],
            ['disable-admin-bar-frontend','Disable Frontend Admin Bar','Hide the WordPress admin toolbar on the public site while keeping wp-admin unchanged.','admin',DisableAdminBarFrontend::class,['admin bar','frontend'],['frontend'],'low',false,[]],
            ['hide-admin-notices','Hide Admin Notices','Hide standard WordPress admin notices for administrators to reduce dashboard clutter.','admin',HideAdminNotices::class,['notices','admin','cleanup'],['admin'],'medium',false,[]],
            ['footer-timezone','Footer Time & Timezone','Show the site-local date, time, and timezone in the WordPress admin footer.','admin',FooterTimezone::class,['timezone','footer','admin'],['admin'],'low',false,[]],
            ['search-visibility-status','Search Visibility Warning','Show a prominent admin-bar warning whenever search engine indexing is disabled.','utilities',SearchVisibilityStatus::class,['seo','indexing','visibility'],['all'],'low',false,[]],
            ['last-login-column','Last Login Column','Record and display each user’s most recent successful login in the Users list.','users',LastLoginColumn::class,['users','login','audit'],['admin'],'low',false,[]],
            ['registration-date-column','Registration Date Column','Show user registration dates in the Users list.','users',RegistrationDateColumn::class,['users','registration','audit'],['admin'],'low',false,[]],
            ['disable-woo-reviews','Disable Product Reviews','Disable WooCommerce product reviews without affecting normal post comments.','woocommerce',DisableReviews::class,['woocommerce','reviews'],['all'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['payment-method-column','Payment Method Column','Show the payment method directly in WooCommerce order lists, including HPOS.','woocommerce',PaymentMethodColumn::class,['woocommerce','orders','hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['order-phone-column','Order Phone Column','Show the billing phone number directly in WooCommerce order lists, including HPOS.','woocommerce',OrderPhoneColumn::class,['woocommerce','orders','phone','hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['order-amount-rules','Minimum / Maximum Order Amount','Set optional minimum and maximum cart totals for checkout.','woocommerce',OrderAmountRules::class,['woocommerce','checkout','minimum','maximum'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['free-shipping-only','Free Shipping Method Control','Hide other shipping methods when free shipping is available.','woocommerce',FreeShippingOnly::class,['woocommerce','shipping','free shipping'],['frontend','ajax','rest'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['buy-now','Buy Now Button','Add a Buy Now button that sends successful add-to-cart actions directly to checkout.','woocommerce',BuyNow::class,['woocommerce','conversion','checkout'],['frontend','ajax'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['cod-rules','COD Amount Rules','Show COD only when the cart total is within configured minimum and maximum amounts.','woocommerce',CodRules::class,['woocommerce','cod','payment'],['frontend','ajax','rest'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['shipping-progress','Free Shipping Progress Bar','Show customers how much more they need to spend to reach a configured free-shipping threshold.','woocommerce',ShippingProgressBar::class,['woocommerce','shipping','progress'],['frontend','ajax'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['fomo-sales-notifications','FOMO Sales Notifications','Cache up to 20 recent paid orders once daily and show lightweight randomized purchase notifications.','woocommerce',FomoSalesNotifications::class,['woocommerce','fomo','sales','notifications'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woocommerce-wishlist','Wishlist','Lightweight WooCommerce wishlist for guests and logged-in users with AJAX toggles, a wishlist page, and optional post-purchase removal.','woocommerce',Wishlist::class,['woocommerce','wishlist','favorites'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
        ];
        foreach($defs as [$slug,$name,$description,$category,$class,$tags,$contexts,$risk,$settings,$dependencies]){
            $r->register(new ModuleDefinition(['slug'=>$slug,'name'=>__($name,'smg-site-suite'),'description'=>__($description,'smg-site-suite'),'category'=>$category,'class'=>$class,'tags'=>$tags,'contexts'=>$contexts,'risk'=>$risk,'has_settings'=>$settings,'dependencies'=>$dependencies]));
        }
        return $r;
    }
}
