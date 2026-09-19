<?php
namespace SMG\SiteSuite\Modules\Users;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class TemporaryLogin implements ModuleInterface {
    private const META = '_smg_site_suite_temp_login';
    private const ACTION = 'smg_temp_login';

    public function register(): void {
        add_action('admin_menu', [$this, 'menu'], 50);
        add_action('admin_post_smg_site_suite_create_temp_login', [$this, 'create']);
        add_action('admin_post_smg_site_suite_revoke_temp_login', [$this, 'revoke']);
        add_action('init', [$this, 'consume'], 1);
        add_action('smg_site_suite_temp_login_cleanup', [$this, 'cleanup']);
    }

    public function menu(): void {
        add_submenu_page(
            'smg-site-suite',
            __('Temporary Login', 'smg-site-suite'),
            __('Temporary Login', 'smg-site-suite'),
            'create_users',
            'smg-site-suite-temp-login',
            [$this, 'render']
        );
    }

    public function render(): void {
        if (!current_user_can('create_users')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>'.esc_html__('Temporary Login', 'smg-site-suite').'</h1>';
        echo '<p>'
            .esc_html__(
                'Create a temporary administrator login link with an expiry. The link can be used once.',
                'smg-site-suite'
            )
            .'</p>';

        $this->renderCreateForm();
        $this->renderExistingAccess();
        $this->renderCreatedLink();

        echo '</div>';
    }

    private function renderCreateForm(): void {
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
        echo '<input type="hidden" name="action" value="smg_site_suite_create_temp_login">';
        wp_nonce_field('smg_site_suite_create_temp_login');

        echo '<table class="form-table">';
        echo '<tr><th><label>'.esc_html__('Email', 'smg-site-suite').'</label></th>';
        echo '<td><input type="email" name="email" required class="regular-text"></td></tr>';

        echo '<tr><th><label>'.esc_html__('Expires in', 'smg-site-suite').'</label></th>';
        echo '<td><select name="hours">';
        echo '<option value="1">1 hour</option>';
        echo '<option value="6">6 hours</option>';
        echo '<option value="24" selected>24 hours</option>';
        echo '<option value="72">72 hours</option>';
        echo '</select></td></tr>';
        echo '</table>';

        submit_button(__('Create Temporary Login', 'smg-site-suite'));
        echo '</form>';
    }

    private function renderExistingAccess(): void {
        $users = get_users([
            'meta_key' => self::META,
            'number' => 100,
            'orderby' => 'registered',
            'order' => 'DESC',
        ]);

        if ($users === []) {
            return;
        }

        $headings = [
            __('User', 'smg-site-suite'),
            __('Email', 'smg-site-suite'),
            __('Expires', 'smg-site-suite'),
            __('Type', 'smg-site-suite'),
            __('Action', 'smg-site-suite'),
        ];

        echo '<h2>'.esc_html__('Active / Recent Temporary Access', 'smg-site-suite').'</h2>';
        echo '<table class="widefat striped" style="max-width:1000px">';
        echo '<thead><tr>';

        foreach ($headings as $heading) {
            echo '<th>'.esc_html($heading).'</th>';
        }

        echo '</tr></thead><tbody>';

        foreach ($users as $user) {
            $data = get_user_meta($user->ID, self::META, true);
            if (!is_array($data)) {
                continue;
            }

            $expires = (int)($data['expires'] ?? 0);
            $type = !empty($data['temporary_user'])
                ? __('Temporary account', 'smg-site-suite')
                : __('Existing account link', 'smg-site-suite');

            $revokeUrl = wp_nonce_url(
                admin_url(
                    'admin-post.php?action=smg_site_suite_revoke_temp_login&user_id='.$user->ID
                ),
                'smg_site_suite_revoke_temp_login_'.$user->ID
            );

            echo '<tr>';
            echo '<td>'.esc_html($user->display_name).'</td>';
            echo '<td>'.esc_html($user->user_email).'</td>';
            echo '<td>'.esc_html($expires > 0 ? wp_date('Y-m-d H:i', $expires) : '—').'</td>';
            echo '<td>'.esc_html($type).'</td>';
            echo '<td><a class="button button-small" href="'.esc_url($revokeUrl).'">'
                .esc_html__('Revoke', 'smg-site-suite')
                .'</a></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    private function renderCreatedLink(): void {
        if (!isset($_GET['token']) || !is_string($_GET['token'])) {
            return;
        }

        $url = add_query_arg(
            [
                self::ACTION => '1',
                'token' => sanitize_text_field(wp_unslash($_GET['token'])),
            ],
            home_url('/')
        );

        echo '<div class="notice notice-success inline">';
        echo '<p><strong>'
            .esc_html__('Temporary login link:', 'smg-site-suite')
            .'</strong></p>';
        echo '<input class="large-text" readonly value="'.esc_attr($url).'">';
        echo '</div>';
    }

    public function create(): void {
        if (!current_user_can('create_users')) {
            wp_die(esc_html__('Insufficient permissions.', 'smg-site-suite'));
        }

        check_admin_referer('smg_site_suite_create_temp_login');

        $email = sanitize_email((string)($_POST['email'] ?? ''));
        $hours = max(1, min(168, absint($_POST['hours'] ?? 24)));

        if ($email === '') {
            wp_die(esc_html__('Valid email required.', 'smg-site-suite'));
        }

        $user = get_user_by('email', $email);
        $created = false;

        if (!$user) {
            $user = $this->createTemporaryAdministrator($email);
            $created = true;
        }

        $token = wp_generate_password(40, false, false);
        $expires = time() + ($hours * HOUR_IN_SECONDS);

        update_user_meta(
            $user->ID,
            self::META,
            [
                'hash' => wp_hash_password($token),
                'expires' => $expires,
                'created_by' => get_current_user_id(),
                'temporary_user' => $created,
            ]
        );

        if (
            $created
            && !wp_next_scheduled(
                'smg_site_suite_temp_login_cleanup',
                [$user->ID]
            )
        ) {
            wp_schedule_single_event(
                $expires + 60,
                'smg_site_suite_temp_login_cleanup',
                [$user->ID]
            );
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => 'smg-site-suite-temp-login',
                    'token' => rawurlencode($token),
                ],
                admin_url('admin.php')
            )
        );
        exit;
    }

    private function createTemporaryAdministrator(string $email): \WP_User {
        $username = sanitize_user(strstr($email, '@', true) ?: 'temp', true);
        $base = $username !== '' ? $username : 'temp';
        $candidate = $base;
        $suffix = 1;

        while (username_exists($candidate)) {
            $candidate = $base.$suffix;
            $suffix++;
        }

        $userId = wp_create_user(
            $candidate,
            wp_generate_password(32, true, true),
            $email
        );

        if (is_wp_error($userId)) {
            wp_die(esc_html($userId->get_error_message()));
        }

        $user = new \WP_User($userId);
        $user->set_role('administrator');
        return $user;
    }

    public function revoke(): void {
        if (!current_user_can('create_users')) {
            wp_die(esc_html__('Insufficient permissions.', 'smg-site-suite'));
        }

        $userId = isset($_GET['user_id']) ? absint($_GET['user_id']) : 0;
        if ($userId <= 0) {
            wp_die(esc_html__('Invalid user.', 'smg-site-suite'));
        }

        check_admin_referer('smg_site_suite_revoke_temp_login_'.$userId);

        $data = get_user_meta($userId, self::META, true);
        if (is_array($data) && !empty($data['temporary_user'])) {
            require_once ABSPATH.'wp-admin/includes/user.php';
            wp_delete_user($userId);
        } else {
            delete_user_meta($userId, self::META);
        }

        wp_safe_redirect(
            add_query_arg('page', 'smg-site-suite-temp-login', admin_url('admin.php'))
        );
        exit;
    }

    public function cleanup(int $userId): void {
        $data = get_user_meta($userId, self::META, true);

        if (!is_array($data) || empty($data['temporary_user'])) {
            return;
        }

        if ((int)($data['expires'] ?? 0) > time()) {
            return;
        }

        require_once ABSPATH.'wp-admin/includes/user.php';
        wp_delete_user($userId);
    }

    public function consume(): void {
        if (!$this->hasLoginRequest()) {
            return;
        }

        $token = sanitize_text_field(wp_unslash($_GET['token']));
        $users = get_users([
            'meta_key' => self::META,
            'number' => 100,
        ]);

        foreach ($users as $user) {
            $data = get_user_meta($user->ID, self::META, true);

            if (!is_array($data) || (int)($data['expires'] ?? 0) < time()) {
                continue;
            }

            if (!wp_check_password($token, (string)($data['hash'] ?? ''))) {
                continue;
            }

            $this->consumeAccessRecord($user->ID, $data);

            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, false, is_ssl());
            wp_safe_redirect(admin_url());
            exit;
        }

        wp_die(
            esc_html__('This temporary login link is invalid or expired.', 'smg-site-suite'),
            '',
            ['response' => 403]
        );
    }

    private function hasLoginRequest(): bool {
        if (!isset($_GET[self::ACTION], $_GET['token'])) {
            return false;
        }

        return '1' === sanitize_text_field(wp_unslash($_GET[self::ACTION]));
    }

    private function consumeAccessRecord(int $userId, array $data): void {
        if (!empty($data['temporary_user'])) {
            $data['hash'] = '';
            update_user_meta($userId, self::META, $data);
            return;
        }

        delete_user_meta($userId, self::META);
    }
}
