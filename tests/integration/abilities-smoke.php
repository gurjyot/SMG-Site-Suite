<?php
if (!defined('ABSPATH')) {
    fwrite(STDERR, "WordPress not loaded\n");
    exit(1);
}

if (!function_exists('wp_get_ability')) {
    fwrite(STDERR, "WordPress Abilities API not available\n");
    exit(1);
}

if (!function_exists('wp_delete_user')) {
    require_once ABSPATH.'wp-admin/includes/user.php';
}

$failures = [];
$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

$admin = get_user_by('login', 'admin');
$assert($admin instanceof WP_User, 'Integration administrator account not found.');
if (!$admin instanceof WP_User) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: {$failure}\n");
    }
    exit(1);
}

wp_set_current_user($admin->ID);

$abilityNames = [
    'smg-site-suite/list-modules',
    'smg-site-suite/get-module',
    'smg-site-suite/activate-module',
    'smg-site-suite/deactivate-module',
    'smg-site-suite/get-module-settings',
    'smg-site-suite/update-module-settings',
];

foreach ($abilityNames as $name) {
    $ability = wp_get_ability($name);
    $assert($ability instanceof WP_Ability, "Missing registered ability: {$name}");

    if ($ability instanceof WP_Ability) {
        $meta = $ability->get_meta();
        $assert(($meta['show_in_rest'] ?? false) === true, "Ability is not REST-visible: {$name}");
        $assert(($meta['mcp']['public'] ?? false) === true, "Ability is not MCP-public: {$name}");
    }
}

$listAbility = wp_get_ability('smg-site-suite/list-modules');
$getAbility = wp_get_ability('smg-site-suite/get-module');
$activateAbility = wp_get_ability('smg-site-suite/activate-module');
$deactivateAbility = wp_get_ability('smg-site-suite/deactivate-module');
$getSettingsAbility = wp_get_ability('smg-site-suite/get-module-settings');
$updateSettingsAbility = wp_get_ability('smg-site-suite/update-module-settings');

$originalActive = (array) get_option('smg_site_suite_active_modules', []);
$originalDashboard = get_option('smg_site_suite_custom_dashboard', null);
$originalOwners = get_option('smg_site_suite_protected_owners', null);
$createdUserIds = [];

try {
    if ($listAbility instanceof WP_Ability) {
        $result = $listAbility->execute([]);
        $assert(!is_wp_error($result), 'List modules ability returned an error.');
        if (is_array($result)) {
            $assert(($result['count'] ?? 0) >= 135, 'List modules ability returned fewer than 135 modules.');
        }
    }

    if ($getAbility instanceof WP_Ability) {
        $result = $getAbility->execute(['slug' => 'reading-time']);
        $assert(!is_wp_error($result), 'Get module ability returned an error.');
        if (is_array($result)) {
            $assert(
                ($result['module']['slug'] ?? '') === 'reading-time',
                'Get module ability returned the wrong module.'
            );
        }
    }

    if ($activateAbility instanceof WP_Ability && $deactivateAbility instanceof WP_Ability) {
        $activate = $activateAbility->execute(['slug' => 'reading-time']);
        $assert(!is_wp_error($activate), 'Activate module ability returned an error.');
        $assert(
            in_array('reading-time', (array) get_option('smg_site_suite_active_modules', []), true),
            'Activate module ability did not persist active state.'
        );

        $deactivate = $deactivateAbility->execute(['slug' => 'reading-time']);
        $assert(!is_wp_error($deactivate), 'Deactivate module ability returned an error.');
        $assert(
            !in_array('reading-time', (array) get_option('smg_site_suite_active_modules', []), true),
            'Deactivate module ability did not persist inactive state.'
        );
    }

    if ($getSettingsAbility instanceof WP_Ability && $updateSettingsAbility instanceof WP_Ability) {
        $settings = $getSettingsAbility->execute(['slug' => 'custom-dashboard-page']);
        $assert(!is_wp_error($settings), 'Get module settings ability returned an error.');

        $updated = $updateSettingsAbility->execute([
            'slug' => 'custom-dashboard-page',
            'settings' => [
                'page_id' => 0,
                'height' => 99999,
                'replace' => true,
            ],
        ]);
        $assert(!is_wp_error($updated), 'Update module settings ability returned an error.');
        if (is_array($updated)) {
            $assert(
                (int) ($updated['settings']['height'] ?? 0) === 4000,
                'Settings ability bypassed module sanitization.'
            );
        }
    }

    $protected = new \SMG\SiteSuite\Modules\Admin\ProtectedOwner();
    $protected->activate();
    $active = (array) get_option('smg_site_suite_active_modules', []);
    $active[] = 'protected-owner';
    update_option(
        'smg_site_suite_active_modules',
        array_values(array_unique($active)),
        false
    );

    $secondId = wp_create_user(
        'smg_agent_admin_'.wp_generate_password(6, false, false),
        wp_generate_password(24, true, true),
        'smg-agent-'.wp_generate_password(6, false, false).'@example.test'
    );

    if (is_wp_error($secondId)) {
        $failures[] = 'Could not create secondary administrator for ability permission test.';
    } else {
        $createdUserIds[] = (int) $secondId;
        $second = get_user_by('id', $secondId);
        if ($second instanceof WP_User) {
            $second->set_role('administrator');
            wp_set_current_user((int) $secondId);

            if ($listAbility instanceof WP_Ability) {
                $blocked = $listAbility->execute([]);
                $assert(
                    is_wp_error($blocked),
                    'Protected Owner did not block Site Suite ability access for another administrator.'
                );
            }
        }
    }
} finally {
    wp_set_current_user($admin->ID);
    update_option('smg_site_suite_active_modules', $originalActive, false);

    if ($originalDashboard === null) {
        delete_option('smg_site_suite_custom_dashboard');
    } else {
        update_option('smg_site_suite_custom_dashboard', $originalDashboard, false);
    }

    if ($originalOwners === null) {
        delete_option('smg_site_suite_protected_owners');
    } else {
        update_option('smg_site_suite_protected_owners', $originalOwners, false);
    }

    foreach ($createdUserIds as $userId) {
        wp_delete_user($userId);
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: {$failure}\n");
    }
    exit(1);
}

echo "SMG Site Suite Abilities API smoke passed.\n";
