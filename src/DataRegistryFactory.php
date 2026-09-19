<?php
namespace SMG\SiteSuite;
use SMG\WPFoundation\Data\DataOwnershipRegistry;
final class DataRegistryFactory {
    public static function make():DataOwnershipRegistry{
        return (new DataOwnershipRegistry())
            ->register('login-branding','option','smg_site_suite_login_branding','configuration')
            ->register('admin-footer','option','smg_site_suite_admin_footer','configuration')
            ->register('dashboard-widgets','option','smg_site_suite_dashboard_widgets','configuration')
            ->register('revision-control','option','smg_site_suite_revision_control','configuration')
            ->register('heartbeat-control','option','smg_site_suite_heartbeat_control','configuration')
            ->register('image-size-control','option','smg_site_suite_image_size_control','configuration')
            ->register('custom-excerpt-length','option','smg_site_suite_excerpt_length','configuration')
            ->register('custom-frontend-css','option','smg_site_suite_frontend_css','configuration')
            ->register('custom-admin-css','option','smg_site_suite_admin_css','configuration')
            ->register('email-sender-identity','option','smg_site_suite_email_sender','configuration')
            ->register('order-amount-rules','option','smg_site_suite_order_amount_rules','configuration')
            ->register('cod-rules','option','smg_site_suite_cod_rules','configuration')
            ->register('fomo-sales-notifications','option','smg_site_suite_fomo_settings','configuration')
            ->register('fomo-sales-notifications','option','smg_site_suite_fomo_snapshot','generated')
            ->register('fomo-sales-notifications','option','smg_site_suite_fomo_snapshot_updated','generated')
            ->register('fomo-sales-notifications','cron','smg_site_suite_fomo_refresh','generated')
            ->register('woocommerce-wishlist','option','smg_site_suite_wishlist_settings','configuration')
            ->register('woocommerce-wishlist','option','smg_site_suite_wishlist_page_id','configuration')
            ->register('woocommerce-wishlist','user_meta','_smg_site_suite_wishlist','user_data')
            ->register('maintenance-mode','option','smg_site_suite_maintenance','configuration')
            ->register('head-body-footer-code','option','smg_site_suite_injected_code','configuration')
            ->register('email-sender-identity','option','smg_site_suite_email_sender','configuration')
            ->register('login-logout-redirects','option','smg_site_suite_login_logout_redirects','configuration');
    }
}
