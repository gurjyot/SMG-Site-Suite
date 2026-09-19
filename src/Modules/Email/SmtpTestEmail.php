<?php
namespace SMG\SiteSuite\Modules\Email;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class SmtpTestEmail implements ModuleInterface {
    public function register():void{
        add_action('admin_menu',[$this,'menu'],75);
        add_action('admin_post_smg_site_suite_send_test_email',[$this,'send']);
    }

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Test Email','smg-site-suite'),__('Test Email','smg-site-suite'),'manage_options','smg-site-suite-test-email',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        $default=wp_get_current_user()->user_email;
        echo '<div class="wrap"><h1>'.esc_html__('Send Test Email','smg-site-suite').'</h1>';
        if(isset($_GET['sent']))echo '<div class="notice notice-success"><p>'.esc_html__('Test email sent successfully.','smg-site-suite').'</p></div>';
        if(isset($_GET['failed']))echo '<div class="notice notice-error"><p>'.esc_html__('WordPress reported that the test email could not be sent.','smg-site-suite').'</p></div>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="smg_site_suite_send_test_email">';
        wp_nonce_field('smg_site_suite_send_test_email');
        echo '<table class="form-table"><tr><th><label>'.esc_html__('Recipient','smg-site-suite').'</label></th><td><input class="regular-text" type="email" name="email" required value="'.esc_attr($default).'"></td></tr></table>';
        submit_button(__('Send Test Email','smg-site-suite'));
        echo '</form></div>';
    }

    public function send():void{
        if(!current_user_can('manage_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_send_test_email');
        $email=sanitize_email((string)($_POST['email']??''));
        if($email==='')wp_die(esc_html__('A valid email address is required.','smg-site-suite'));
        $sent=wp_mail($email,__('SMG Site Suite test email','smg-site-suite'),__('If you received this message, WordPress mail delivery is working.','smg-site-suite'));
        wp_safe_redirect(add_query_arg(['page'=>'smg-site-suite-test-email',$sent?'sent':'failed'=>'1'],admin_url('admin.php')));
        exit;
    }
}
