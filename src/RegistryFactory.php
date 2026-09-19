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
            ['disable-comments',__('Disable Comments','smg-site-suite'),__('Disable comments, pingbacks, trackbacks, and comment admin surfaces.','smg-site-suite'),'content',DisableComments::class,['comments','spam'],['all'],'low',false,[]],
            ['duplicate-content',__('Duplicate Content','smg-site-suite'),__('Duplicate posts, pages, and public custom post types from the list screen.','smg-site-suite'),'content',DuplicateContent::class,['duplicate','clone'],['admin'],'medium',false,[]],
            ['login-branding',__('Login Branding','smg-site-suite'),__('Customize the WordPress login logo and background.','smg-site-suite'),'admin',LoginBranding::class,['login','branding'],['admin'],'low',true,[]],
            ['admin-footer',__('Admin Footer','smg-site-suite'),__('Customize the WordPress admin footer text and version visibility.','smg-site-suite'),'admin',AdminFooter::class,['footer','admin'],['admin'],'low',true,[]],
            ['dashboard-widgets',__('Dashboard Widgets','smg-site-suite'),__('Hide selected default WordPress dashboard widgets.','smg-site-suite'),'admin',DashboardWidgets::class,['dashboard','widgets'],['admin'],'low',true,[]],
            ['revision-control',__('Revision Control','smg-site-suite'),__('Limit or disable post revisions without editing wp-config.php.','smg-site-suite'),'content',RevisionControl::class,['revisions','content'],['all'],'low',true,[]],
            ['heartbeat-control',__('Heartbeat Control','smg-site-suite'),__('Adjust WordPress Heartbeat frequency to reduce unnecessary admin requests.','smg-site-suite'),'performance',HeartbeatControl::class,['heartbeat','performance'],['admin','ajax'],'low',true,[]],
            ['safe-svg-upload',__('Safe SVG Upload','smg-site-suite'),__('Sanitize SVG files before WordPress accepts the upload.','smg-site-suite'),'media',SafeSvgUpload::class,['svg','media','upload'],['admin','ajax','rest'],'medium',false,['classes'=>['DOMDocument']]],
            ['image-size-control',__('Image Size Control','smg-site-suite'),__('Disable selected WordPress-generated image sizes for future uploads.','smg-site-suite'),'media',ImageSizeControl::class,['images','media','sizes'],['admin','ajax','rest'],'low',true,[]],
            ['disable-emojis',__('Disable Emoji Assets','smg-site-suite'),__('Remove WordPress emoji scripts, styles, and related resource hints.','smg-site-suite'),'performance',DisableEmojis::class,['emoji','performance','assets'],['all'],'low',false,[]],
            ['disable-embeds',__('Disable WordPress Embeds','smg-site-suite'),__('Remove WordPress oEmbed discovery, embed scripts, and oEmbed REST endpoints.','smg-site-suite'),'performance',DisableEmbeds::class,['embeds','oembed','performance'],['all'],'low',false,[]],
            ['hide-wp-version',__('Hide WordPress Version','smg-site-suite'),__('Remove WordPress generator output and core version query strings without breaking plugin/theme cache busting.','smg-site-suite'),'security',HideWpVersion::class,['version','security','generator'],['all'],'low',false,[]],
            ['disable-xml-rpc',__('Disable XML-RPC','smg-site-suite'),__('Disable XML-RPC and remove pingback discovery headers.','smg-site-suite'),'security',DisableXmlRpc::class,['xmlrpc','pingback','security'],['all'],'low',false,[]],
            ['disable-application-passwords',__('Disable Application Passwords','smg-site-suite'),__('Disable WordPress Application Password authentication when it is not needed.','smg-site-suite'),'security',DisableApplicationPasswords::class,['application passwords','security','auth'],['all'],'low',false,[]],
            ['clean-wp-head',__('Clean WordPress Head','smg-site-suite'),__('Remove legacy RSD, WLW, shortlink, and adjacent-post discovery tags.','smg-site-suite'),'performance',CleanWpHead::class,['head','cleanup','performance'],['frontend'],'low',false,[]],
            ['disable-feeds',__('Disable Feeds','smg-site-suite'),__('Disable WordPress RSS/Atom feeds and remove feed discovery links.','smg-site-suite'),'performance',DisableFeeds::class,['feeds','rss','performance'],['all'],'low',false,[]],
            ['disable-author-archives',__('Disable Author Archives','smg-site-suite'),__('Redirect author archive URLs to the homepage and remove author archive links.','smg-site-suite'),'security',DisableAuthorArchives::class,['authors','security','archives'],['frontend'],'medium',false,[]],
            ['external-links-new-tab',__('External Links in New Tab','smg-site-suite'),__('Open external HTTP/HTTPS links in a new tab with noopener protection.','smg-site-suite'),'content',ExternalLinksNewTab::class,['external links','content','new tab'],['frontend'],'low',false,[]],
            ['custom-excerpt-length',__('Custom Excerpt Length','smg-site-suite'),__('Control the word length of automatically generated WordPress excerpts.','smg-site-suite'),'utilities',CustomExcerptLength::class,['excerpt','content','length'],['all'],'low',true,[]],
            ['custom-frontend-css',__('Custom Frontend CSS','smg-site-suite'),__('Add site-wide CSS without editing the active theme.','smg-site-suite'),'content',CustomFrontendCss::class,['css','frontend','design'],['frontend'],'medium',true,[]],
            ['custom-admin-css',__('Custom Admin CSS','smg-site-suite'),__('Add wp-admin CSS without editing WordPress, plugins, or the theme.','smg-site-suite'),'admin',CustomAdminCss::class,['css','admin','design'],['admin'],'medium',true,[]],
            ['auto-publish-missed-schedules',__('Recover Missed Scheduled Posts','smg-site-suite'),__('Publish a small batch of overdue scheduled posts when normal WP-Cron misses them.','smg-site-suite'),'content',AutoPublishMissedSchedules::class,['schedule','publishing','cron'],['frontend'],'low',false,[]],
            ['disable-big-image-scaling',__('Disable Big Image Scaling','smg-site-suite'),__('Stop WordPress from automatically scaling down very large uploaded images.','smg-site-suite'),'media',DisableBigImageScaling::class,['images','media','scaling'],['admin','ajax','rest'],'low',false,[]],
            ['disable-self-pingbacks',__('Disable Self Pingbacks','smg-site-suite'),__('Prevent WordPress from pinging your own site when linking internally.','smg-site-suite'),'content',DisableSelfPingbacks::class,['pingback','content','links'],['all'],'low',false,[]],
            ['remove-comment-website-field',__('Remove Comment Website Field','smg-site-suite'),__('Remove the website URL field from the default WordPress comment form.','smg-site-suite'),'content',RemoveCommentWebsiteField::class,['comments','spam','form'],['frontend'],'low',false,[]],
            ['email-sender-identity',__('Email Sender Identity','smg-site-suite'),__('Set a custom default From name and From email for WordPress mail.','smg-site-suite'),'email',EmailSenderIdentity::class,['email','sender','mail'],['all'],'low',true,[]],
            ['system-summary',__('System Summary','smg-site-suite'),__('Add a read-only Site Suite system summary page for WordPress, PHP, database, theme, memory, timezone, and debug status.','smg-site-suite'),'admin',SystemSummary::class,['system','diagnostics','admin'],['admin'],'low',false,[]],
            ['maintenance-mode',__('Maintenance Mode','smg-site-suite'),__('Show visitors a 503 maintenance page while logged-in administrators keep access.','smg-site-suite'),'utilities',MaintenanceMode::class,['maintenance','503','site'],['frontend'],'medium',true,[]],
            ['head-body-footer-code',__('Head / Body / Footer Code','smg-site-suite'),__('Insert trusted administrator code into the public head, body-open, or footer locations.','smg-site-suite'),'utilities',HeadBodyFooterCode::class,['code','analytics','scripts'],['frontend'],'high',true,[]],
            ['generic-login-errors',__('Generic Login Errors','smg-site-suite'),__('Hide detailed WordPress login failure reasons behind a generic error message.','smg-site-suite'),'security',GenericLoginErrors::class,['login','security','errors'],['all'],'low',false,[]],
            ['search-posts-only',__('Search Posts Only','smg-site-suite'),__('Limit the default front-end WordPress search to posts.','smg-site-suite'),'content',SearchPostsOnly::class,['search','posts','content'],['frontend'],'low',false,[]],
            ['disable-texturize',__('Disable Texturize','smg-site-suite'),__('Disable WordPress smart quotes and automatic typographic character substitutions.','smg-site-suite'),'content',DisableTexturize::class,['texturize','editor','content'],['all'],'low',false,[]],
            ['remove-recent-comments-css',__('Remove Recent Comments CSS','smg-site-suite'),__('Stop the legacy Recent Comments widget from adding inline CSS to the page head.','smg-site-suite'),'performance',RemoveRecentCommentsCss::class,['comments','css','performance'],['frontend'],'low',false,[]],
            ['login-logout-redirects',__('Login / Logout Redirects','smg-site-suite'),__('Set optional destinations after successful login and logout.','smg-site-suite'),'users',LoginLogoutRedirects::class,['login','logout','redirect'],['all'],'medium',true,[]],
            ['disable-dashicons-frontend',__('Disable Dashicons for Guests','smg-site-suite'),__('Stop loading Dashicons on the public frontend for logged-out visitors.','smg-site-suite'),'performance',DisableDashiconsFrontend::class,['dashicons','performance','assets'],['frontend'],'low',false,[]],
            ['disable-file-editing',__('Disable Theme / Plugin File Editors','smg-site-suite'),__('Remove access to the built-in WordPress theme and plugin code editors.','smg-site-suite'),'security',DisableFileEditing::class,['file editor','security','admin'],['admin'],'low',false,[]],
            ['show-ids',__('Show IDs','smg-site-suite'),__('Add ID columns to WordPress post type and taxonomy list tables.','smg-site-suite'),'admin',ShowIds::class,['ids','admin','columns'],['admin'],'low',false,[]],
            ['active-plugins-first',__('Active Plugins First','smg-site-suite'),__('Sort active plugins to the top of the Plugins screen.','smg-site-suite'),'admin',ActivePluginsFirst::class,['plugins','admin','productivity'],['admin'],'low',false,[]],
            ['featured-image-column',__('Featured Image Column','smg-site-suite'),__('Show featured-image thumbnails in supported content list tables.','smg-site-suite'),'admin',FeaturedImageColumn::class,['featured image','admin','columns'],['admin'],'low',false,[]],
            ['disable-admin-bar-frontend',__('Disable Frontend Admin Bar','smg-site-suite'),__('Hide the WordPress admin toolbar on the public site while keeping wp-admin unchanged.','smg-site-suite'),'admin',DisableAdminBarFrontend::class,['admin bar','frontend'],['frontend'],'low',false,[]],
            ['hide-admin-notices',__('Hide Admin Notices','smg-site-suite'),__('Hide standard WordPress admin notices for administrators to reduce dashboard clutter.','smg-site-suite'),'admin',HideAdminNotices::class,['notices','admin','cleanup'],['admin'],'medium',false,[]],
            ['footer-timezone',__('Footer Time & Timezone','smg-site-suite'),__('Show the site-local date, time, and timezone in the WordPress admin footer.','smg-site-suite'),'admin',FooterTimezone::class,['timezone','footer','admin'],['admin'],'low',false,[]],
            ['search-visibility-status',__('Search Visibility Warning','smg-site-suite'),__('Show a prominent admin-bar warning whenever search engine indexing is disabled.','smg-site-suite'),'utilities',SearchVisibilityStatus::class,['seo','indexing','visibility'],['all'],'low',false,[]],
            ['last-login-column',__('Last Login Column','smg-site-suite'),__('Record and display each user’s most recent successful login in the Users list.','smg-site-suite'),'users',LastLoginColumn::class,['users','login','audit'],['admin'],'low',false,[]],
            ['registration-date-column',__('Registration Date Column','smg-site-suite'),__('Show user registration dates in the Users list.','smg-site-suite'),'users',RegistrationDateColumn::class,['users','registration','audit'],['admin'],'low',false,[]],
            ['disable-woo-reviews',__('Disable Product Reviews','smg-site-suite'),__('Disable WooCommerce product reviews without affecting normal post comments.','smg-site-suite'),'woocommerce',DisableReviews::class,['woocommerce','reviews'],['all'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['payment-method-column',__('Payment Method Column','smg-site-suite'),__('Show the payment method directly in WooCommerce order lists, including HPOS.','smg-site-suite'),'woocommerce',PaymentMethodColumn::class,['woocommerce','orders','hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['order-phone-column',__('Order Phone Column','smg-site-suite'),__('Show the billing phone number directly in WooCommerce order lists, including HPOS.','smg-site-suite'),'woocommerce',OrderPhoneColumn::class,['woocommerce',__('orders','smg-site-suite'),__('phone','smg-site-suite'),'hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['order-amount-rules',__('Minimum / Maximum Order Amount','smg-site-suite'),__('Set optional minimum and maximum cart totals for checkout.','smg-site-suite'),'woocommerce',OrderAmountRules::class,['woocommerce',__('checkout','smg-site-suite'),__('minimum','smg-site-suite'),'maximum','compat:store-api'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['free-shipping-only',__('Free Shipping Method Control','smg-site-suite'),__('Hide other shipping methods when free shipping is available.','smg-site-suite'),'woocommerce',FreeShippingOnly::class,['woocommerce','shipping','free shipping'],['frontend','ajax','rest'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['buy-now',__('Buy Now Button','smg-site-suite'),__('Add a Buy Now button that sends successful add-to-cart actions directly to checkout.','smg-site-suite'),'woocommerce',BuyNow::class,['woocommerce',__('conversion','smg-site-suite'),__('checkout','smg-site-suite'),'compat:classic-checkout'],['frontend','ajax'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['cod-rules',__('COD Amount Rules','smg-site-suite'),__('Show COD only when the cart total is within configured minimum and maximum amounts.','smg-site-suite'),'woocommerce',CodRules::class,['woocommerce','cod','payment'],['frontend','ajax','rest'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['shipping-progress',__('Free Shipping Progress Bar','smg-site-suite'),__('Show customers how much more they need to spend to reach a configured free-shipping threshold.','smg-site-suite'),'woocommerce',ShippingProgressBar::class,['woocommerce','shipping','progress'],['frontend','ajax'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['fomo-sales-notifications',__('FOMO Sales Notifications','smg-site-suite'),__('Build a daily snapshot of recent paid orders and use it for purchase notifications.','smg-site-suite'),'woocommerce',FomoSalesNotifications::class,['woocommerce',__('fomo','smg-site-suite'),__('sales','smg-site-suite'),'notifications'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woocommerce-wishlist',__('Wishlist','smg-site-suite'),__('Add a wishlist for guests and customers, including sharing and optional removal after purchase.','smg-site-suite'),'woocommerce',Wishlist::class,['woocommerce','wishlist','favorites'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['catalog-mode',__('Catalog Mode','smg-site-suite'),__('Disable purchasing globally or only for logged-out visitors, with optional price hiding.','smg-site-suite'),'woocommerce',CatalogMode::class,['woocommerce','catalog','purchasing'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['whatsapp-enquiry',__('WhatsApp Product Enquiry','smg-site-suite'),__('Add WhatsApp enquiry buttons with product details in the message.','smg-site-suite'),'woocommerce',WhatsappEnquiry::class,['woocommerce','whatsapp','enquiry'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['quantity-rules',__('Quantity Rules','smg-site-suite'),__('Set global minimum, maximum, and step quantities for WooCommerce purchases.','smg-site-suite'),'woocommerce',QuantityRules::class,['woocommerce','quantity','cart'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['product-tabs-control',__('Product Tabs Control','smg-site-suite'),__('Hide or rename standard WooCommerce product tabs.','smg-site-suite'),'woocommerce',ProductTabsControl::class,['woocommerce','product tabs','reviews'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['auto-apply-coupon',__('Auto Apply Coupon','smg-site-suite'),__('Automatically apply a configured WooCommerce coupon in cart and checkout.','smg-site-suite'),'woocommerce',AutoApplyCoupon::class,['woocommerce',__('coupon','smg-site-suite'),__('discount','smg-site-suite'),'compat:store-api'],['frontend','rest','ajax'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['thank-you-message',__('Thank-you Message','smg-site-suite'),__('Display a custom message on the WooCommerce order received page.','smg-site-suite'),'woocommerce',ThankYouMessage::class,['woocommerce','thank you','order'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['rename-payment-methods',__('Rename Payment Methods','smg-site-suite'),__('Rename visible WooCommerce payment gateway titles without editing gateway plugins.','smg-site-suite'),'woocommerce',RenamePaymentMethods::class,['woocommerce','payment','gateway'],['frontend','ajax','rest'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['rename-shipping-methods',__('Rename Shipping Methods','smg-site-suite'),__('Rename shipping rates by method or rate instance ID.','smg-site-suite'),'woocommerce',RenameShippingMethods::class,['woocommerce','shipping','method'],['frontend','ajax','rest'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['direct-checkout',__('Direct Checkout','smg-site-suite'),__('Send standard add-to-cart actions straight to checkout.','smg-site-suite'),'woocommerce',DirectCheckout::class,['woocommerce',__('checkout','smg-site-suite'),__('conversion','smg-site-suite'),'compat:classic-checkout'],['frontend','ajax'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['checkout-field-controls',__('Checkout Field Controls','smg-site-suite'),__('Hide selected classic checkout fields and control billing phone requirement.','smg-site-suite'),'woocommerce',CheckoutFieldControls::class,['woocommerce',__('checkout','smg-site-suite'),__('fields','smg-site-suite'),'compat:classic-checkout'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['direct-checkout-links',__('Direct Checkout Links','smg-site-suite'),__('Generate campaign links that add a selected product and quantity, optionally clear the cart, and go straight to checkout.','smg-site-suite'),'woocommerce',DirectCheckoutLinks::class,['woocommerce',__('checkout','smg-site-suite'),__('links','smg-site-suite'),'campaigns','compat:classic-checkout'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['url-coupons',__('URL Coupons','smg-site-suite'),__('Apply WooCommerce coupons from a configurable URL parameter with optional cart or checkout redirect.','smg-site-suite'),'woocommerce',UrlCoupons::class,['woocommerce','coupon','url','compat:classic-checkout'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['checkout-text-customizer',__('Checkout Text Customizer','smg-site-suite'),__('Customize classic checkout button text and selected field placeholders.','smg-site-suite'),'woocommerce',CheckoutTextCustomizer::class,['woocommerce',__('checkout','smg-site-suite'),__('labels','smg-site-suite'),'compat:classic-checkout'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woo-asset-control',__('WooCommerce Asset Control','smg-site-suite'),__('Unload selected WooCommerce styles and scripts on non-store pages.','smg-site-suite'),'woocommerce',WooAssetControl::class,['woocommerce','performance','assets'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['cart-fragments-control',__('Cart Fragments Control','smg-site-suite'),__('Keep WooCommerce cart fragments only where needed, use defaults, or disable them.','smg-site-suite'),'woocommerce',CartFragmentsControl::class,['woocommerce','performance','fragments'],['frontend'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['my-account-redirects',__('My Account Redirects','smg-site-suite'),__('Set optional WooCommerce login, registration, and logout destinations.','smg-site-suite'),'woocommerce',MyAccountRedirects::class,['woocommerce','account','redirect'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['product-price-history',__('Product Price History','smg-site-suite'),__('Record recent WooCommerce regular and sale price changes in product meta and show them in product admin.','smg-site-suite'),'woocommerce',ProductPriceHistory::class,['woocommerce','price','history'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['disable-marketplace-suggestions',__('Disable Marketplace Suggestions','smg-site-suite'),__('Hide WooCommerce marketplace recommendation prompts in wp-admin.','smg-site-suite'),'woocommerce',DisableMarketplaceSuggestions::class,['woocommerce','admin','marketplace'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['coupon-role-restrictions',__('Coupon Role Restrictions','smg-site-suite'),__('Restrict individual WooCommerce coupons to selected WordPress user roles.','smg-site-suite'),'woocommerce',CouponRoleRestrictions::class,['woocommerce','coupon','roles'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['coupon-maximum-discount',__('Coupon Maximum Discount','smg-site-suite'),__('Add an optional per-coupon maximum discount amount with a configurable default.','smg-site-suite'),'woocommerce',CouponMaximumDiscount::class,['woocommerce','coupon','maximum discount'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['custom-order-statuses',__('Custom Order Statuses','smg-site-suite'),__('Add custom WooCommerce order statuses from a slug and label.','smg-site-suite'),'woocommerce',CustomOrderStatuses::class,['woocommerce','orders','status'],['all'],'medium',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['security-headers',__('Security Headers','smg-site-suite'),__('Send a small set of HTTP security headers from WordPress.','smg-site-suite'),'security',SecurityHeaders::class,['security','headers','http'],['all'],'medium',true,[]],
            ['redirect-manager',__('Redirect Manager','smg-site-suite'),__('Create local 301 redirects from old paths to new paths.','smg-site-suite'),'utilities',RedirectManager::class,['redirects','seo','404'],['frontend'],'medium',true,[]],
            ['404-tracker',__('404 Tracker','smg-site-suite'),__('Log recent 404 URLs with hit counts, last-seen time, and referrers.','smg-site-suite'),'utilities',NotFoundTracker::class,['404','seo','tracking'],['frontend','admin'],'low',true,[]],
            ['admin-menu-organizer',__('Admin Menu Organizer','smg-site-suite'),__('Choose which wp-admin menus and submenus client administrators can see.','smg-site-suite'),'admin',AdminMenuOrganizer::class,['admin menu','organizer','client admin'],['admin'],'medium',false,[]],
            ['protected-owner',__('Protected Owner','smg-site-suite'),__('Protect selected administrator accounts from changes made by other administrators.','smg-site-suite'),'admin',ProtectedOwner::class,['owner',__('admin','smg-site-suite'),__('protection','smg-site-suite'),'white label'],['all'],'high',false,[]],
            ['custom-dashboard-page',__('Custom Dashboard Page','smg-site-suite'),__('Replace the WordPress dashboard with any published page built in Bricks, Elementor, Beaver Builder, Gutenberg, or another builder.','smg-site-suite'),'admin',CustomDashboardPage::class,['dashboard',__('builder','smg-site-suite'),__('white label','smg-site-suite'),'client'],['admin','frontend'],'medium',true,[]],
            ['smtp-test-email',__('SMTP Test Email','smg-site-suite'),__('Send a test WordPress email to verify the active mail/SMTP configuration.','smg-site-suite'),'email',SmtpTestEmail::class,['smtp','email','test'],['admin'],'low',false,[]],
            ['disable-user-account',__('Disable User Account','smg-site-suite'),__('Temporarily block a user from logging in without deleting the account.','smg-site-suite'),'users',DisableUserAccount::class,['users','access','disable'],['all'],'medium',false,[]],
            ['hide-admin-bar-by-role',__('Hide Admin Bar by Role','smg-site-suite'),__('Hide the public WordPress admin bar for selected user roles.','smg-site-suite'),'users',HideAdminBarByRole::class,['users','roles','admin bar'],['frontend'],'low',true,[]],
            ['replace-media',__('Replace Media','smg-site-suite'),__('Replace an attachment file while preserving its attachment ID and content references.','smg-site-suite'),'media',ReplaceMedia::class,['media','replace','attachment'],['admin'],'medium',false,[]],
            ['multiple-user-roles',__('Multiple User Roles','smg-site-suite'),__('Assign more than one WordPress role to a user from the profile screen.','smg-site-suite'),'users',MultipleUserRoles::class,['users','roles','permissions'],['admin'],'medium',false,[]],
            ['environment-indicator',__('Environment Indicator','smg-site-suite'),__('Show a configurable environment label in the WordPress admin bar for production, staging, or development sites.','smg-site-suite'),'admin',EnvironmentIndicator::class,['environment','staging','admin bar'],['all'],'low',true,[]],
            ['cron-viewer',__('Cron Viewer','smg-site-suite'),__('Read-only view of scheduled WordPress cron events, hooks, schedules, and arguments.','smg-site-suite'),'admin',CronViewer::class,['cron','diagnostics','admin'],['admin'],'low',false,[]],
            ['database-table-sizes',__('Database Table Sizes','smg-site-suite'),__('Read-only database table size and row-count overview for diagnostics.','smg-site-suite'),'admin',DatabaseTableSizes::class,['database','diagnostics','storage'],['admin'],'low',false,[]],
            ['duplicate-navigation-menu',__('Duplicate Navigation Menu','smg-site-suite'),__('Duplicate a classic WordPress navigation menu and its items from Appearance.','smg-site-suite'),'admin',DuplicateNavigationMenu::class,['menus','duplicate','admin'],['admin'],'low',false,[]],
            ['auto-featured-image',__('Auto Featured Image','smg-site-suite'),__('Use the first attached image as the featured image when selected post types do not already have one.','smg-site-suite'),'content',AutoFeaturedImage::class,['featured image','media','content'],['admin'],'low',true,[]],
            ['default-featured-image',__('Default Featured Image','smg-site-suite'),__('Use a Media Library image as a non-destructive fallback featured image for selected post types.','smg-site-suite'),'content',DefaultFeaturedImage::class,['featured image','fallback','media'],['all'],'low',true,[]],
            ['public-preview-drafts',__('Public Preview Drafts','smg-site-suite'),__('Create expiring public preview links for draft, pending, or scheduled content.','smg-site-suite'),'content',PublicPreviewDrafts::class,['preview','draft','content'],['all'],'medium',true,[]],
            ['reading-time',__('Reading Time','smg-site-suite'),__('Add a reading-time shortcode with configurable speed and label.','smg-site-suite'),'content',ReadingTime::class,['reading time','content','shortcode'],['all'],'low',true,[]],
            ['external-permalinks',__('External Permalinks','smg-site-suite'),__('Optionally point individual posts, pages, or CPT entries to external URLs.','smg-site-suite'),'content',ExternalPermalinks::class,['permalink','redirect','external'],['all'],'medium',false,[]],
            ['local-user-avatar',__('Local User Avatar','smg-site-suite'),__('Use Media Library images as local WordPress user avatars instead of external avatar services.','smg-site-suite'),'users',LocalUserAvatar::class,['avatar','users','media'],['all'],'low',false,[]],
            ['custom-body-classes',__('Custom Body Classes','smg-site-suite'),__('Add managed global CSS classes to the public body element.','smg-site-suite'),'utilities',CustomBodyClasses::class,['css','body','classes'],['frontend'],'low',true,[]],
            ['admin-taxonomy-filters',__('Admin Taxonomy Filters','smg-site-suite'),__('Add hierarchical taxonomy dropdown filters to supported post-type list tables.','smg-site-suite'),'admin',AdminTaxonomyFilters::class,['taxonomy','admin','filters'],['admin'],'low',false,[]],
            ['content-expiration',__('Content Expiration','smg-site-suite'),__('Schedule content to move to draft, private, or trash at a chosen future time.','smg-site-suite'),'content',ContentExpiration::class,['content','expiration','schedule'],['all'],'medium',false,[]],
            ['media-categories',__('Media Categories','smg-site-suite'),__('Add a hierarchical Media Categories taxonomy to WordPress attachments.','smg-site-suite'),'media',MediaCategories::class,['media','taxonomy','categories'],['all'],'low',false,[]],
            ['media-details-columns',__('Media Details Columns','smg-site-suite'),__('Show image dimensions and file sizes in the Media Library list view.','smg-site-suite'),'media',MediaDetailsColumns::class,['media','dimensions','filesize'],['admin'],'low',false,[]],
            ['debug-log-viewer',__('Debug Log Viewer','smg-site-suite'),__('Read the latest WordPress debug.log lines from a protected admin-only screen.','smg-site-suite'),'admin',DebugLogViewer::class,['debug','logs','diagnostics'],['admin'],'medium',false,[]],
            ['internal-content-notes',__('Internal Content Notes','smg-site-suite'),__('Attach private admin-only notes to posts, pages, and other editable content.','smg-site-suite'),'content',InternalContentNotes::class,['notes','content','admin'],['admin'],'low',false,[]],
            ['require-featured-image',__('Require Featured Image','smg-site-suite'),__('Prevent selected post types from publishing without a featured image.','smg-site-suite'),'content',RequireFeaturedImage::class,['featured image','editorial','publish'],['admin'],'medium',true,[]],
            ['content-metrics-columns',__('Content Metrics Columns','smg-site-suite'),__('Show word count and last-modified time in editable content list tables.','smg-site-suite'),'admin',ContentMetricsColumns::class,['content','columns','word count'],['admin'],'low',false,[]],
            ['upload-size-limit',__('Upload Size Limit','smg-site-suite'),__('Apply an optional Site Suite maximum upload size below the server limit.','smg-site-suite'),'media',UploadSizeLimit::class,['media','upload','limit'],['admin','ajax','rest'],'low',true,[]],
            ['plugin-theme-notes',__('Plugin & Theme Notes','smg-site-suite'),__('Store private agency notes explaining installed plugins, themes, dependencies, and update cautions.','smg-site-suite'),'admin',PluginThemeNotes::class,['plugins',__('themes','smg-site-suite'),__('notes','smg-site-suite'),'agency'],['admin'],'low',false,[]],
            ['critical-plugin-protection',__('Critical Plugin Protection','smg-site-suite'),__('Prevent other administrators from deactivating selected plugins and exclude them from automatic updates.','smg-site-suite'),'admin',CriticalPluginProtection::class,['plugins','protection','updates'],['admin'],'high',true,[]],
            ['site-health-extensions',__('Site Health Extensions','smg-site-suite'),__('Add Site Suite checks for HTTPS, debug mode, search visibility, and WP-Cron to WordPress Site Health.','smg-site-suite'),'admin',SiteHealthExtensions::class,['site health','diagnostics','security'],['admin'],'low',false,[]],
            ['woo-order-notes-column',__('Woo Order Notes Column','smg-site-suite'),__('Show the latest WooCommerce order note in legacy and HPOS order list tables.','smg-site-suite'),'woocommerce',OrderNotesColumn::class,['woocommerce',__('orders','smg-site-suite'),__('notes','smg-site-suite'),'hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woo-product-thumbnail-column',__('Woo Product Thumbnail Column','smg-site-suite'),__('Ensure product thumbnails are visible in the WooCommerce product list.','smg-site-suite'),'woocommerce',ProductThumbnailColumn::class,['woocommerce','products','thumbnail'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['woo-customer-lifetime-orders',__('Woo Customer Lifetime Orders','smg-site-suite'),__('Show WooCommerce order count and lifetime spend in the WordPress Users list.','smg-site-suite'),'woocommerce',CustomerLifetimeOrders::class,['woocommerce',__('customers','smg-site-suite'),__('orders','smg-site-suite'),'lifetime'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['estimated-delivery-message',__('Estimated Delivery Message','smg-site-suite'),__('Show a configurable delivery-time message on WooCommerce product pages.','smg-site-suite'),'woocommerce',EstimatedDeliveryMessage::class,['woocommerce','delivery','product'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['custom-stock-messages',__('Custom Stock Messages','smg-site-suite'),__('Customize in-stock, low-stock, and out-of-stock WooCommerce availability messages.','smg-site-suite'),'woocommerce',CustomStockMessages::class,['woocommerce','stock','inventory'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['checkout-success-whatsapp',__('Checkout Success WhatsApp','smg-site-suite'),__('Show an order-aware WhatsApp button on the WooCommerce thank-you page.','smg-site-suite'),'woocommerce',CheckoutSuccessWhatsapp::class,['woocommerce',__('whatsapp','smg-site-suite'),__('checkout','smg-site-suite'),'order'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['login-as-user',__('Login as User','smg-site-suite'),__('Allow a Protected Owner to impersonate a non-protected user with a one-time return session.','smg-site-suite'),'users',LoginAsUser::class,['users',__('impersonation','smg-site-suite'),__('support','smg-site-suite'),'owner'],['admin'],'high',false,[]],
            ['plugin-update-freeze',__('Plugin Update Freeze','smg-site-suite'),__('Turn off automatic updates for selected plugins and limit manual updates to Protected Owners.','smg-site-suite'),'admin',PluginUpdateFreeze::class,['plugins',__('updates','smg-site-suite'),__('freeze','smg-site-suite'),'owner'],['admin'],'high',true,[]],
            ['product-badge-manager',__('Product Badge Manager','smg-site-suite'),__('Show configurable New, Low Stock, and Out of Stock badges on WooCommerce products.','smg-site-suite'),'woocommerce',ProductBadgeManager::class,['woocommerce',__('products','smg-site-suite'),__('badges','smg-site-suite'),'stock'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['product-sku-column',__('Product SKU Column','smg-site-suite'),__('Show product SKUs in the WooCommerce product list.','smg-site-suite'),'woocommerce',ProductSkuColumn::class,['woocommerce',__('products','smg-site-suite'),__('sku','smg-site-suite'),'admin'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['order-item-summary-column',__('Order Item Summary Column','smg-site-suite'),__('Show a compact product/quantity summary in WooCommerce legacy and HPOS order lists.','smg-site-suite'),'woocommerce',OrderItemSummaryColumn::class,['woocommerce',__('orders','smg-site-suite'),__('items','smg-site-suite'),'hpos'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['coupon-usage-column',__('Coupon Usage Column','smg-site-suite'),__('Show WooCommerce coupon usage counts and expiry dates in the coupon list.','smg-site-suite'),'woocommerce',CouponUsageColumn::class,['woocommerce',__('coupons','smg-site-suite'),__('usage','smg-site-suite'),'admin'],['admin'],'low',false,['plugins'=>['woocommerce/woocommerce.php']]],
            ['empty-cart-button',__('Empty Cart Button','smg-site-suite'),__('Add a nonce-protected Empty Cart button to the classic WooCommerce cart.','smg-site-suite'),'woocommerce',EmptyCartButton::class,['woocommerce',__('cart','smg-site-suite'),__('button','smg-site-suite'),'compat:classic-checkout'],['frontend'],'low',true,['plugins'=>['woocommerce/woocommerce.php']]],
            ['autoload-options-report',__('Autoload Options Report','smg-site-suite'),__('Read-only report of the largest autoloaded WordPress options and their approximate sizes.','smg-site-suite'),'admin',AutoloadOptionsReport::class,['database',__('autoload','smg-site-suite'),__('performance','smg-site-suite'),'diagnostics'],['admin'],'low',false,[]],
            ['transient-viewer',__('Transient Viewer','smg-site-suite'),__('Read-only overview of stored WordPress transients, sizes, and expiry times.','smg-site-suite'),'admin',TransientViewer::class,['transients','cache','diagnostics'],['admin'],'low',false,[]],
            ['rewrite-rules-viewer',__('Rewrite Rules Viewer','smg-site-suite'),__('Read-only view of the WordPress rewrite-rule table for permalink diagnostics.','smg-site-suite'),'admin',RewriteRulesViewer::class,['rewrite rules','permalinks','diagnostics'],['admin'],'low',false,[]],
            ['site-inventory-export',__('Site Inventory Export','smg-site-suite'),__('Export a settings-free JSON inventory of WordPress, runtime, theme, plugins, and active Site Suite modules.','smg-site-suite'),'admin',SiteInventoryExport::class,['inventory',__('export','smg-site-suite'),__('agency','smg-site-suite'),'diagnostics'],['admin'],'low',false,[]],
            ['auto-update-email-controls',__('Auto-Update Email Controls','smg-site-suite'),__('Optionally suppress WordPress core, plugin, and theme automatic update notification emails.','smg-site-suite'),'email',AutoUpdateEmailControls::class,['email','updates','notifications'],['all'],'low',true,[]],
            ['sanitize-upload-filenames',__('Sanitize Upload Filenames','smg-site-suite'),__('Normalize new upload filenames to lowercase ASCII kebab-case.','smg-site-suite'),'media',SanitizeUploadFilenames::class,['media','filenames','uploads'],['admin','ajax','rest'],'low',false,[]],
            ['temporary-login',__('Temporary Login','smg-site-suite'),__('Create one-use expiring administrator access links for support or development.','smg-site-suite'),'users',TemporaryLogin::class,['temporary login','support','access'],['all'],'high',false,[]],
            ['activity-log-lite',__('Activity Log Lite','smg-site-suite'),__('Keep a recent local history of logins, plugin/theme changes, and content saves.','smg-site-suite'),'admin',ActivityLogLite::class,['activity','audit','log'],['all'],'medium',true,[]],
            ['smtp-mailer',__('SMTP Mailer','smg-site-suite'),__('Route WordPress mail through a configurable SMTP server.','smg-site-suite'),'email',SmtpMailer::class,['smtp','email','delivery'],['all'],'medium',true,[]],
            ['mail-log',__('Mail Log','smg-site-suite'),__('Log recent WordPress mail attempts and failures.','smg-site-suite'),'email',MailLog::class,['email','mail','log'],['all'],'medium',true,[]],
            ['robots-txt-manager',__('Robots.txt Manager','smg-site-suite'),__('Override the virtual WordPress robots.txt with managed custom content.','smg-site-suite'),'utilities',RobotsTxtManager::class,['robots','seo','crawler'],['all'],'medium',true,[]],
            ['ads-txt-manager',__('ads.txt Manager','smg-site-suite'),__('Serve managed ads.txt content virtually from /ads.txt.','smg-site-suite'),'utilities',AdsTxtManager::class,['ads.txt','advertising','publisher'],['frontend'],'low',true,[]],
        ];
        foreach($defs as [$slug,$name,$description,$category,$class,$tags,$contexts,$risk,$settings,$dependencies]){
            $r->register(new ModuleDefinition(['slug'=>$slug,'name'=>$name,'description'=>$description,'category'=>$category,'class'=>$class,'tags'=>$tags,'contexts'=>$contexts,'risk'=>$risk,'has_settings'=>$settings,'dependencies'=>$dependencies]));
        }
        return $r;
    }
}
