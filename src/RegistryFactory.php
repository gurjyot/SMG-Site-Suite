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
use SMG\SiteSuite\Modules\Utilities\MaintenanceMode;
use SMG\SiteSuite\Modules\Utilities\HeadBodyFooterCode;
use SMG\SiteSuite\Modules\Security\GenericLoginErrors;
use SMG\SiteSuite\Modules\Content\SearchPostsOnly;
use SMG\SiteSuite\Modules\Content\DisableTexturize;
use SMG\SiteSuite\Modules\Performance\RemoveRecentCommentsCss;
use SMG\SiteSuite\Modules\Users\LoginLogoutRedirects;
use SMG\SiteSuite\Modules\WooCommerce\CatalogMode;
use SMG\SiteSuite\Modules\WooCommerce\WhatsappEnquiry;
use SMG\SiteSuite\Modules\WooCommerce\QuantityRules;
use SMG\SiteSuite\Modules\WooCommerce\ProductTabsControl;
use SMG\SiteSuite\Modules\WooCommerce\AutoApplyCoupon;
use SMG\SiteSuite\Modules\WooCommerce\ThankYouMessage;
use SMG\SiteSuite\Modules\WooCommerce\RenamePaymentMethods;
use SMG\SiteSuite\Modules\WooCommerce\RenameShippingMethods;
use SMG\SiteSuite\Modules\WooCommerce\DirectCheckout;
use SMG\SiteSuite\Modules\WooCommerce\CheckoutFieldControls;
use SMG\SiteSuite\Modules\WooCommerce\DirectCheckoutLinks;
use SMG\SiteSuite\Modules\WooCommerce\UrlCoupons;
use SMG\SiteSuite\Modules\WooCommerce\CheckoutTextCustomizer;
use SMG\SiteSuite\Modules\WooCommerce\WooAssetControl;
use SMG\SiteSuite\Modules\WooCommerce\CartFragmentsControl;
use SMG\SiteSuite\Modules\WooCommerce\MyAccountRedirects;
use SMG\SiteSuite\Modules\WooCommerce\ProductPriceHistory;
use SMG\SiteSuite\Modules\WooCommerce\DisableMarketplaceSuggestions;
use SMG\SiteSuite\Modules\WooCommerce\CouponRoleRestrictions;
use SMG\SiteSuite\Modules\WooCommerce\CouponMaximumDiscount;
use SMG\SiteSuite\Modules\WooCommerce\CustomOrderStatuses;
use SMG\SiteSuite\Modules\Security\SecurityHeaders;
use SMG\SiteSuite\Modules\Utilities\RedirectManager;
use SMG\SiteSuite\Modules\Utilities\NotFoundTracker;
use SMG\SiteSuite\Modules\Admin\AdminMenuOrganizer;
use SMG\SiteSuite\Modules\Media\SanitizeUploadFilenames;
use SMG\SiteSuite\Modules\Users\TemporaryLogin;
use SMG\SiteSuite\Modules\Admin\ActivityLogLite;
use SMG\SiteSuite\Modules\Email\SmtpMailer;
use SMG\SiteSuite\Modules\Email\MailLog;
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
            ['maintenance-mode','Maintenance Mode','Return a lightweight 503 maintenance page to visitors while administrators retain access.','utilities',MaintenanceMode::class,['maintenance','503','site'],['frontend'],'medium',true,[]],
            ['head-body-footer-code','Head / Body / Footer Code','Insert trusted administrator code into the public head, body-open, or footer locations.','utilities',HeadBodyFooterCode::class,['code','analytics','scripts'],['frontend'],'high',true,[]],
            ['generic-login-errors','Generic Login Errors','Hide detailed WordPress login failure reasons behind a generic error message.','security',GenericLoginErrors::class,['login','security','errors'],['all'],'low',false,[]],
            ['search-posts-only','Search Posts Only','Limit the default front-end WordPress search to posts.','content',SearchPostsOnly::class,['search','posts','content'],['frontend'],'low',false,[]],
            ['disable-texturize','Disable Texturize','Disable WordPress smart quotes and automatic typographic character substitutions.','content',DisableTexturize::class,['texturize','editor','content'],['all'],'low',false,[]],
            ['remove-recent-comments-css','Remove Recent Comments CSS','Stop the legacy Recent Comments widget from adding inline CSS to the page head.','performance',RemoveRecentCommentsCss::class,['comments','css','performance'],['frontend'],'low',false,[]],
            ['login-logout-redirects','Login / Logout Redirects','Set optional destinations after successful login and logout.','users',LoginLogoutRedirects::class,['login','logout','redirect'],['all'],'medium',true,[]],
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
            ['catalog-mode','Catalog Mode','Disable purchasing globally or only for logged-out visitors, with optional price hiding.','woocommerce',CatalogMode::class,['woocommerce','catalog','purchasing'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['whatsapp-enquiry','WhatsApp Product Enquiry','Add lightweight WhatsApp enquiry buttons with product-aware message templates.','woocommerce',WhatsappEnquiry::class,['woocommerce','whatsapp','enquiry'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['quantity-rules','Quantity Rules','Set global minimum, maximum, and step quantities for WooCommerce purchases.','woocommerce',QuantityRules::class,['woocommerce','quantity','cart'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['product-tabs-control','Product Tabs Control','Hide or rename standard WooCommerce product tabs.','woocommerce',ProductTabsControl::class,['woocommerce','product tabs','reviews'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['auto-apply-coupon','Auto Apply Coupon','Automatically apply a configured WooCommerce coupon in cart and checkout.','woocommerce',AutoApplyCoupon::class,['woocommerce','coupon','discount'],['frontend','rest','ajax'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['thank-you-message','Thank-you Message','Display a custom message on the WooCommerce order received page.','woocommerce',ThankYouMessage::class,['woocommerce','thank you','order'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['rename-payment-methods','Rename Payment Methods','Rename visible WooCommerce payment gateway titles without editing gateway plugins.','woocommerce',RenamePaymentMethods::class,['woocommerce','payment','gateway'],['frontend','ajax','rest'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['rename-shipping-methods','Rename Shipping Methods','Rename shipping rates by method or rate instance ID.','woocommerce',RenameShippingMethods::class,['woocommerce','shipping','method'],['frontend','ajax','rest'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['direct-checkout','Direct Checkout','Redirect ordinary add-to-cart actions directly to checkout.','woocommerce',DirectCheckout::class,['woocommerce','checkout','conversion'],['frontend','ajax'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['checkout-field-controls','Checkout Field Controls','Hide selected classic checkout fields and control billing phone requirement.','woocommerce',CheckoutFieldControls::class,['woocommerce','checkout','fields'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['direct-checkout-links','Direct Checkout Links','Generate campaign links that add a selected product and quantity, optionally clear the cart, and go straight to checkout.','woocommerce',DirectCheckoutLinks::class,['woocommerce','checkout','links','campaigns'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['url-coupons','URL Coupons','Apply WooCommerce coupons from a configurable URL parameter with optional cart or checkout redirect.','woocommerce',UrlCoupons::class,['woocommerce','coupon','url'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['checkout-text-customizer','Checkout Text Customizer','Customize classic checkout button text and selected field placeholders.','woocommerce',CheckoutTextCustomizer::class,['woocommerce','checkout','labels'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woo-asset-control','WooCommerce Asset Control','Unload selected WooCommerce styles and scripts on non-store pages.','woocommerce',WooAssetControl::class,['woocommerce','performance','assets'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['cart-fragments-control','Cart Fragments Control','Keep WooCommerce cart fragments only where needed, use defaults, or disable them.','woocommerce',CartFragmentsControl::class,['woocommerce','performance','fragments'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['my-account-redirects','My Account Redirects','Set optional WooCommerce login, registration, and logout destinations.','woocommerce',MyAccountRedirects::class,['woocommerce','account','redirect'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['product-price-history','Product Price History','Record recent WooCommerce regular and sale price changes in product meta and show them in product admin.','woocommerce',ProductPriceHistory::class,['woocommerce','price','history'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['disable-marketplace-suggestions','Disable Marketplace Suggestions','Hide WooCommerce marketplace recommendation prompts in wp-admin.','woocommerce',DisableMarketplaceSuggestions::class,['woocommerce','admin','marketplace'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['coupon-role-restrictions','Coupon Role Restrictions','Restrict individual WooCommerce coupons to selected WordPress user roles.','woocommerce',CouponRoleRestrictions::class,['woocommerce','coupon','roles'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['coupon-maximum-discount','Coupon Maximum Discount','Add an optional per-coupon maximum discount amount with a configurable default.','woocommerce',CouponMaximumDiscount::class,['woocommerce','coupon','maximum discount'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['custom-order-statuses','Custom Order Statuses','Register lightweight custom WooCommerce order statuses from simple slug/label definitions.','woocommerce',CustomOrderStatuses::class,['woocommerce','orders','status'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['security-headers','Security Headers','Add conservative HTTP security headers without requiring server configuration changes.','security',SecurityHeaders::class,['security','headers','http'],['all'],'medium',true,[]],
            ['redirect-manager','Redirect Manager','Create lightweight local 301 redirect rules from old paths to new paths.','utilities',RedirectManager::class,['redirects','seo','404'],['frontend'],'medium',true,[]],
            ['404-tracker','404 Tracker','Keep a bounded local log of missing URLs, hit counts, last-seen time, and referrers.','utilities',NotFoundTracker::class,['404','seo','tracking'],['frontend','admin'],'low',true,[]],
            ['admin-menu-organizer','Admin Menu Organizer','Hide selected top-level wp-admin menu items for administrators.','admin',AdminMenuOrganizer::class,['admin menu','organizer','cleanup'],['admin'],'low',true,[]],
            ['sanitize-upload-filenames','Sanitize Upload Filenames','Normalize new upload filenames to lowercase ASCII kebab-case.','media',SanitizeUploadFilenames::class,['media','filenames','uploads'],['admin','ajax','rest'],'low',false,[]],
            ['temporary-login','Temporary Login','Create one-use expiring administrator access links for support or development.','users',TemporaryLogin::class,['temporary login','support','access'],['all'],'high',false,[]],
            ['activity-log-lite','Activity Log Lite','Keep a bounded local history of logins, plugin/theme changes, and content saves.','admin',ActivityLogLite::class,['activity','audit','log'],['all'],'medium',true,[]],
            ['smtp-mailer','SMTP Mailer','Route WordPress mail through a configurable SMTP server.','email',SmtpMailer::class,['smtp','email','delivery'],['all'],'medium',true,[]],
            ['mail-log','Mail Log','Keep a bounded local log of WordPress mail attempts and failures.','email',MailLog::class,['email','mail','log'],['all'],'medium',true,[]],
        ];
        foreach($defs as [$slug,$name,$description,$category,$class,$tags,$contexts,$risk,$settings,$dependencies]){
            $r->register(new ModuleDefinition(['slug'=>$slug,'name'=>__($name,'smg-site-suite'),'description'=>__($description,'smg-site-suite'),'category'=>$category,'class'=>$class,'tags'=>$tags,'contexts'=>$contexts,'risk'=>$risk,'has_settings'=>$settings,'dependencies'=>$dependencies]));
        }
        return $r;
    }
}
