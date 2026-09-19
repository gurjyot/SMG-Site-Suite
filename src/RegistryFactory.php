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
use SMG\SiteSuite\Modules\Utilities\RobotsTxtManager;
use SMG\SiteSuite\Modules\Utilities\AdsTxtManager;
use SMG\SiteSuite\Modules\Admin\ProtectedOwner;
use SMG\SiteSuite\Modules\Admin\CustomDashboardPage;
use SMG\SiteSuite\Modules\Email\SmtpTestEmail;
use SMG\SiteSuite\Modules\Users\DisableUserAccount;
use SMG\SiteSuite\Modules\Users\HideAdminBarByRole;
use SMG\SiteSuite\Modules\Media\ReplaceMedia;
use SMG\SiteSuite\Modules\Users\MultipleUserRoles;
use SMG\SiteSuite\Modules\Admin\EnvironmentIndicator;
use SMG\SiteSuite\Modules\Admin\CronViewer;
use SMG\SiteSuite\Modules\Admin\DatabaseTableSizes;
use SMG\SiteSuite\Modules\Admin\DuplicateNavigationMenu;
use SMG\SiteSuite\Modules\Content\AutoFeaturedImage;
use SMG\SiteSuite\Modules\Email\AutoUpdateEmailControls;
use SMG\SiteSuite\Modules\Content\DefaultFeaturedImage;
use SMG\SiteSuite\Modules\Content\PublicPreviewDrafts;
use SMG\SiteSuite\Modules\Content\ReadingTime;
use SMG\SiteSuite\Modules\Content\ExternalPermalinks;
use SMG\SiteSuite\Modules\Users\LocalUserAvatar;
use SMG\SiteSuite\Modules\Utilities\CustomBodyClasses;
use SMG\SiteSuite\Modules\Admin\AdminTaxonomyFilters;
use SMG\SiteSuite\Modules\Content\ContentExpiration;
use SMG\SiteSuite\Modules\Media\MediaCategories;
use SMG\SiteSuite\Modules\Media\MediaDetailsColumns;
use SMG\SiteSuite\Modules\Admin\DebugLogViewer;
use SMG\SiteSuite\Modules\Content\InternalContentNotes;
use SMG\SiteSuite\Modules\Content\RequireFeaturedImage;
use SMG\SiteSuite\Modules\Admin\ContentMetricsColumns;
use SMG\SiteSuite\Modules\Media\UploadSizeLimit;
use SMG\SiteSuite\Modules\Admin\PluginThemeNotes;
use SMG\SiteSuite\Modules\Admin\CriticalPluginProtection;
use SMG\SiteSuite\Modules\Admin\SiteHealthExtensions;
use SMG\SiteSuite\Modules\WooCommerce\OrderNotesColumn;
use SMG\SiteSuite\Modules\WooCommerce\ProductThumbnailColumn;
use SMG\SiteSuite\Modules\WooCommerce\CustomerLifetimeOrders;
use SMG\SiteSuite\Modules\WooCommerce\EstimatedDeliveryMessage;
use SMG\SiteSuite\Modules\WooCommerce\CustomStockMessages;
use SMG\SiteSuite\Modules\WooCommerce\CheckoutSuccessWhatsapp;
use SMG\SiteSuite\Modules\Users\LoginAsUser;
use SMG\SiteSuite\Modules\Admin\PluginUpdateFreeze;
use SMG\SiteSuite\Modules\WooCommerce\ProductBadgeManager;
use SMG\SiteSuite\Modules\WooCommerce\ProductSkuColumn;
use SMG\SiteSuite\Modules\WooCommerce\OrderItemSummaryColumn;
use SMG\SiteSuite\Modules\WooCommerce\CouponUsageColumn;
use SMG\SiteSuite\Modules\WooCommerce\EmptyCartButton;
use SMG\SiteSuite\Modules\Admin\AutoloadOptionsReport;
use SMG\SiteSuite\Modules\Admin\TransientViewer;
use SMG\SiteSuite\Modules\Admin\RewriteRulesViewer;
use SMG\SiteSuite\Modules\Admin\SiteInventoryExport;
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
            ['order-amount-rules','Minimum / Maximum Order Amount','Set optional minimum and maximum cart totals for checkout.','woocommerce',OrderAmountRules::class,['woocommerce','checkout','minimum','maximum','store-api'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['free-shipping-only','Free Shipping Method Control','Hide other shipping methods when free shipping is available.','woocommerce',FreeShippingOnly::class,['woocommerce','shipping','free shipping'],['frontend','ajax','rest'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['buy-now','Buy Now Button','Add a Buy Now button that sends successful add-to-cart actions directly to checkout.','woocommerce',BuyNow::class,['woocommerce','conversion','checkout','classic-checkout'],['frontend','ajax'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['cod-rules','COD Amount Rules','Show COD only when the cart total is within configured minimum and maximum amounts.','woocommerce',CodRules::class,['woocommerce','cod','payment'],['frontend','ajax','rest'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['shipping-progress','Free Shipping Progress Bar','Show customers how much more they need to spend to reach a configured free-shipping threshold.','woocommerce',ShippingProgressBar::class,['woocommerce','shipping','progress'],['frontend','ajax'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['fomo-sales-notifications','FOMO Sales Notifications','Cache up to 20 recent paid orders once daily and show lightweight randomized purchase notifications.','woocommerce',FomoSalesNotifications::class,['woocommerce','fomo','sales','notifications'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woocommerce-wishlist','Wishlist','Lightweight WooCommerce wishlist for guests and logged-in users with AJAX toggles, a wishlist page, and optional post-purchase removal.','woocommerce',Wishlist::class,['woocommerce','wishlist','favorites'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['catalog-mode','Catalog Mode','Disable purchasing globally or only for logged-out visitors, with optional price hiding.','woocommerce',CatalogMode::class,['woocommerce','catalog','purchasing'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['whatsapp-enquiry','WhatsApp Product Enquiry','Add lightweight WhatsApp enquiry buttons with product-aware message templates.','woocommerce',WhatsappEnquiry::class,['woocommerce','whatsapp','enquiry'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['quantity-rules','Quantity Rules','Set global minimum, maximum, and step quantities for WooCommerce purchases.','woocommerce',QuantityRules::class,['woocommerce','quantity','cart'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['product-tabs-control','Product Tabs Control','Hide or rename standard WooCommerce product tabs.','woocommerce',ProductTabsControl::class,['woocommerce','product tabs','reviews'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['auto-apply-coupon','Auto Apply Coupon','Automatically apply a configured WooCommerce coupon in cart and checkout.','woocommerce',AutoApplyCoupon::class,['woocommerce','coupon','discount','store-api'],['frontend','rest','ajax'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['thank-you-message','Thank-you Message','Display a custom message on the WooCommerce order received page.','woocommerce',ThankYouMessage::class,['woocommerce','thank you','order'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['rename-payment-methods','Rename Payment Methods','Rename visible WooCommerce payment gateway titles without editing gateway plugins.','woocommerce',RenamePaymentMethods::class,['woocommerce','payment','gateway'],['frontend','ajax','rest'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['rename-shipping-methods','Rename Shipping Methods','Rename shipping rates by method or rate instance ID.','woocommerce',RenameShippingMethods::class,['woocommerce','shipping','method'],['frontend','ajax','rest'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['direct-checkout','Direct Checkout','Redirect ordinary add-to-cart actions directly to checkout.','woocommerce',DirectCheckout::class,['woocommerce','checkout','conversion','classic-checkout'],['frontend','ajax'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['checkout-field-controls','Checkout Field Controls','Hide selected classic checkout fields and control billing phone requirement.','woocommerce',CheckoutFieldControls::class,['woocommerce','checkout','fields','classic-checkout'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['direct-checkout-links','Direct Checkout Links','Generate campaign links that add a selected product and quantity, optionally clear the cart, and go straight to checkout.','woocommerce',DirectCheckoutLinks::class,['woocommerce','checkout','links','campaigns'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['url-coupons','URL Coupons','Apply WooCommerce coupons from a configurable URL parameter with optional cart or checkout redirect.','woocommerce',UrlCoupons::class,['woocommerce','coupon','url'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['checkout-text-customizer','Checkout Text Customizer','Customize classic checkout button text and selected field placeholders.','woocommerce',CheckoutTextCustomizer::class,['woocommerce','checkout','labels','classic-checkout'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
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
            ['admin-menu-organizer','Admin Menu Organizer','Visually choose which live wp-admin menus and submenus ordinary administrators can see.','admin',AdminMenuOrganizer::class,['admin menu','organizer','client admin'],['admin'],'medium',false,[]],
            ['protected-owner','Protected Owner','Protect designated administrator accounts from being edited, demoted, removed, or deleted by ordinary administrators.','admin',ProtectedOwner::class,['owner','admin','protection','white label'],['all'],'high',false,[]],
            ['custom-dashboard-page','Custom Dashboard Page','Replace the WordPress dashboard with any published page built in Bricks, Elementor, Beaver Builder, Gutenberg, or another builder.','admin',CustomDashboardPage::class,['dashboard','builder','white label','client'],['admin','frontend'],'medium',true,[]],
            ['smtp-test-email','SMTP Test Email','Send a test WordPress email to verify the active mail/SMTP configuration.','email',SmtpTestEmail::class,['smtp','email','test'],['admin'],'low',false,[]],
            ['disable-user-account','Disable User Account','Temporarily block a user from logging in without deleting the account.','users',DisableUserAccount::class,['users','access','disable'],['all'],'medium',false,[]],
            ['hide-admin-bar-by-role','Hide Admin Bar by Role','Hide the public WordPress admin bar for selected user roles.','users',HideAdminBarByRole::class,['users','roles','admin bar'],['frontend'],'low',true,[]],
            ['replace-media','Replace Media','Replace an attachment file while preserving its attachment ID and content references.','media',ReplaceMedia::class,['media','replace','attachment'],['admin'],'medium',false,[]],
            ['multiple-user-roles','Multiple User Roles','Assign more than one WordPress role to a user from the profile screen.','users',MultipleUserRoles::class,['users','roles','permissions'],['admin'],'medium',false,[]],
            ['environment-indicator','Environment Indicator','Show a configurable environment label in the WordPress admin bar for production, staging, or development sites.','admin',EnvironmentIndicator::class,['environment','staging','admin bar'],['all'],'low',true,[]],
            ['cron-viewer','Cron Viewer','Read-only view of scheduled WordPress cron events, hooks, schedules, and arguments.','admin',CronViewer::class,['cron','diagnostics','admin'],['admin'],'low',false,[]],
            ['database-table-sizes','Database Table Sizes','Read-only database table size and row-count overview for diagnostics.','admin',DatabaseTableSizes::class,['database','diagnostics','storage'],['admin'],'low',false,[]],
            ['duplicate-navigation-menu','Duplicate Navigation Menu','Duplicate a classic WordPress navigation menu and its items from Appearance.','admin',DuplicateNavigationMenu::class,['menus','duplicate','admin'],['admin'],'low',false,[]],
            ['auto-featured-image','Auto Featured Image','Use the first attached image as the featured image when selected post types do not already have one.','content',AutoFeaturedImage::class,['featured image','media','content'],['admin'],'low',true,[]],
            ['default-featured-image','Default Featured Image','Use a Media Library image as a non-destructive fallback featured image for selected post types.','content',DefaultFeaturedImage::class,['featured image','fallback','media'],['all'],'low',true,[]],
            ['public-preview-drafts','Public Preview Drafts','Create expiring public preview links for draft, pending, or scheduled content.','content',PublicPreviewDrafts::class,['preview','draft','content'],['all'],'medium',true,[]],
            ['reading-time','Reading Time','Provide a lightweight reading-time shortcode with configurable reading speed and label.','content',ReadingTime::class,['reading time','content','shortcode'],['all'],'low',true,[]],
            ['external-permalinks','External Permalinks','Optionally point individual posts, pages, or CPT entries to external URLs.','content',ExternalPermalinks::class,['permalink','redirect','external'],['all'],'medium',false,[]],
            ['local-user-avatar','Local User Avatar','Use Media Library images as local WordPress user avatars instead of external avatar services.','users',LocalUserAvatar::class,['avatar','users','media'],['all'],'low',false,[]],
            ['custom-body-classes','Custom Body Classes','Add managed global CSS classes to the public body element.','utilities',CustomBodyClasses::class,['css','body','classes'],['frontend'],'low',true,[]],
            ['admin-taxonomy-filters','Admin Taxonomy Filters','Add hierarchical taxonomy dropdown filters to supported post-type list tables.','admin',AdminTaxonomyFilters::class,['taxonomy','admin','filters'],['admin'],'low',false,[]],
            ['content-expiration','Content Expiration','Schedule content to move to draft, private, or trash at a chosen future time.','content',ContentExpiration::class,['content','expiration','schedule'],['all'],'medium',false,[]],
            ['media-categories','Media Categories','Add a hierarchical Media Categories taxonomy to WordPress attachments.','media',MediaCategories::class,['media','taxonomy','categories'],['all'],'low',false,[]],
            ['media-details-columns','Media Details Columns','Show image dimensions and file sizes in the Media Library list view.','media',MediaDetailsColumns::class,['media','dimensions','filesize'],['admin'],'low',false,[]],
            ['debug-log-viewer','Debug Log Viewer','Read the latest WordPress debug.log lines from a protected admin-only screen.','admin',DebugLogViewer::class,['debug','logs','diagnostics'],['admin'],'medium',false,[]],
            ['internal-content-notes','Internal Content Notes','Attach private admin-only notes to posts, pages, and other editable content.','content',InternalContentNotes::class,['notes','content','admin'],['admin'],'low',false,[]],
            ['require-featured-image','Require Featured Image','Prevent selected post types from publishing without a featured image.','content',RequireFeaturedImage::class,['featured image','editorial','publish'],['admin'],'medium',true,[]],
            ['content-metrics-columns','Content Metrics Columns','Show word count and last-modified time in editable content list tables.','admin',ContentMetricsColumns::class,['content','columns','word count'],['admin'],'low',false,[]],
            ['upload-size-limit','Upload Size Limit','Apply an optional Site Suite maximum upload size below the server limit.','media',UploadSizeLimit::class,['media','upload','limit'],['admin','ajax','rest'],'low',true,[]],
            ['plugin-theme-notes','Plugin & Theme Notes','Store private agency notes explaining installed plugins, themes, dependencies, and update cautions.','admin',PluginThemeNotes::class,['plugins','themes','notes','agency'],['admin'],'low',false,[]],
            ['critical-plugin-protection','Critical Plugin Protection','Protect selected plugins from ordinary administrator deactivation and automatic updates.','admin',CriticalPluginProtection::class,['plugins','protection','updates'],['admin'],'high',true,[]],
            ['site-health-extensions','Site Health Extensions','Add Site Suite checks for HTTPS, debug mode, search visibility, and WP-Cron to WordPress Site Health.','admin',SiteHealthExtensions::class,['site health','diagnostics','security'],['admin'],'low',false,[]],
            ['woo-order-notes-column','Woo Order Notes Column','Show the latest WooCommerce order note in legacy and HPOS order list tables.','woocommerce',OrderNotesColumn::class,['woocommerce','orders','notes','hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woo-product-thumbnail-column','Woo Product Thumbnail Column','Ensure product thumbnails are visible in the WooCommerce product list.','woocommerce',ProductThumbnailColumn::class,['woocommerce','products','thumbnail'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woo-customer-lifetime-orders','Woo Customer Lifetime Orders','Show WooCommerce order count and lifetime spend in the WordPress Users list.','woocommerce',CustomerLifetimeOrders::class,['woocommerce','customers','orders','lifetime'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['estimated-delivery-message','Estimated Delivery Message','Show a configurable delivery-time message on WooCommerce product pages.','woocommerce',EstimatedDeliveryMessage::class,['woocommerce','delivery','product'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['custom-stock-messages','Custom Stock Messages','Customize in-stock, low-stock, and out-of-stock WooCommerce availability messages.','woocommerce',CustomStockMessages::class,['woocommerce','stock','inventory'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['checkout-success-whatsapp','Checkout Success WhatsApp','Show an order-aware WhatsApp button on the WooCommerce thank-you page.','woocommerce',CheckoutSuccessWhatsapp::class,['woocommerce','whatsapp','checkout','order'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['login-as-user','Login as User','Allow a Protected Owner to impersonate a non-protected user with a one-time return session.','users',LoginAsUser::class,['users','impersonation','support','owner'],['admin'],'high',false,[]],
            ['plugin-update-freeze','Plugin Update Freeze','Freeze selected plugins from automatic updates and block ordinary administrators from updating them manually.','admin',PluginUpdateFreeze::class,['plugins','updates','freeze','owner'],['admin'],'high',true,[]],
            ['product-badge-manager','Product Badge Manager','Show configurable New, Low Stock, and Out of Stock badges on WooCommerce products.','woocommerce',ProductBadgeManager::class,['woocommerce','products','badges','stock'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['product-sku-column','Product SKU Column','Show product SKUs in the WooCommerce product list.','woocommerce',ProductSkuColumn::class,['woocommerce','products','sku','admin'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['order-item-summary-column','Order Item Summary Column','Show a compact product/quantity summary in WooCommerce legacy and HPOS order lists.','woocommerce',OrderItemSummaryColumn::class,['woocommerce','orders','items','hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['coupon-usage-column','Coupon Usage Column','Show WooCommerce coupon usage counts and expiry dates in the coupon list.','woocommerce',CouponUsageColumn::class,['woocommerce','coupons','usage','admin'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['empty-cart-button','Empty Cart Button','Add a nonce-protected Empty Cart button to the classic WooCommerce cart.','woocommerce',EmptyCartButton::class,['woocommerce','cart','button','classic-checkout'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['autoload-options-report','Autoload Options Report','Read-only report of the largest autoloaded WordPress options and their approximate sizes.','admin',AutoloadOptionsReport::class,['database','autoload','performance','diagnostics'],['admin'],'low',false,[]],
            ['transient-viewer','Transient Viewer','Read-only overview of stored WordPress transients, sizes, and expiry times.','admin',TransientViewer::class,['transients','cache','diagnostics'],['admin'],'low',false,[]],
            ['rewrite-rules-viewer','Rewrite Rules Viewer','Read-only view of the WordPress rewrite-rule table for permalink diagnostics.','admin',RewriteRulesViewer::class,['rewrite rules','permalinks','diagnostics'],['admin'],'low',false,[]],
            ['site-inventory-export','Site Inventory Export','Export a settings-free JSON inventory of WordPress, runtime, theme, plugins, and active Site Suite modules.','admin',SiteInventoryExport::class,['inventory','export','agency','diagnostics'],['admin'],'low',false,[]],
            ['auto-update-email-controls','Auto-Update Email Controls','Optionally suppress WordPress core, plugin, and theme automatic update notification emails.','email',AutoUpdateEmailControls::class,['email','updates','notifications'],['all'],'low',true,[]],
            ['sanitize-upload-filenames','Sanitize Upload Filenames','Normalize new upload filenames to lowercase ASCII kebab-case.','media',SanitizeUploadFilenames::class,['media','filenames','uploads'],['admin','ajax','rest'],'low',false,[]],
            ['temporary-login','Temporary Login','Create one-use expiring administrator access links for support or development.','users',TemporaryLogin::class,['temporary login','support','access'],['all'],'high',false,[]],
            ['activity-log-lite','Activity Log Lite','Keep a bounded local history of logins, plugin/theme changes, and content saves.','admin',ActivityLogLite::class,['activity','audit','log'],['all'],'medium',true,[]],
            ['smtp-mailer','SMTP Mailer','Route WordPress mail through a configurable SMTP server.','email',SmtpMailer::class,['smtp','email','delivery'],['all'],'medium',true,[]],
            ['mail-log','Mail Log','Keep a bounded local log of WordPress mail attempts and failures.','email',MailLog::class,['email','mail','log'],['all'],'medium',true,[]],
            ['robots-txt-manager','Robots.txt Manager','Override the virtual WordPress robots.txt with managed custom content.','utilities',RobotsTxtManager::class,['robots','seo','crawler'],['all'],'medium',true,[]],
            ['ads-txt-manager','ads.txt Manager','Serve managed ads.txt content virtually from /ads.txt.','utilities',AdsTxtManager::class,['ads.txt','advertising','publisher'],['frontend'],'low',true,[]],
        ];
        foreach($defs as [$slug,$name,$description,$category,$class,$tags,$contexts,$risk,$settings,$dependencies]){
            $r->register(new ModuleDefinition(['slug'=>$slug,'name'=>__($name,'smg-site-suite'),'description'=>__($description,'smg-site-suite'),'category'=>$category,'class'=>$class,'tags'=>$tags,'contexts'=>$contexts,'risk'=>$risk,'has_settings'=>$settings,'dependencies'=>$dependencies]));
        }
        return $r;
    }
}
