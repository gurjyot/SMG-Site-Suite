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
    'smg-site-suite/get-system-summary',
    'smg-site-suite/list-cron-events',
    'smg-site-suite/list-404s',
    'smg-site-suite/list-redirects',
    'smg-site-suite/upsert-redirect',
    'smg-site-suite/delete-redirect',
    'smg-site-suite/get-site-inventory',
    'smg-site-suite/get-database-table-sizes',
    'smg-site-suite/list-rewrite-rules',
    'smg-site-suite/get-site-health',
    'smg-site-suite/list-presets',
    'smg-site-suite/apply-preset',
];

foreach ($abilityNames as $name) {
    $ability = wp_get_ability($name);
    $assert($ability instanceof WP_Ability, "Missing registered ability: {$name}");

    if ($ability instanceof WP_Ability) {
        $meta = $ability->get_meta();
        $assert(
            ($meta['show_in_rest'] ?? false) === true,
            "Ability is not REST-visible: {$name}"
        );
        $assert(
            ($meta['mcp']['public'] ?? false) === true,
            "Ability is not MCP-public: {$name}"
        );
    }
}

$listAbility = wp_get_ability('smg-site-suite/list-modules');
$getAbility = wp_get_ability('smg-site-suite/get-module');
$activateAbility = wp_get_ability('smg-site-suite/activate-module');
$deactivateAbility = wp_get_ability('smg-site-suite/deactivate-module');
$getSettingsAbility = wp_get_ability('smg-site-suite/get-module-settings');
$updateSettingsAbility = wp_get_ability('smg-site-suite/update-module-settings');
$systemSummaryAbility = wp_get_ability('smg-site-suite/get-system-summary');
$cronAbility = wp_get_ability('smg-site-suite/list-cron-events');
$notFoundAbility = wp_get_ability('smg-site-suite/list-404s');
$listRedirectsAbility = wp_get_ability('smg-site-suite/list-redirects');
$upsertRedirectAbility = wp_get_ability('smg-site-suite/upsert-redirect');
$deleteRedirectAbility = wp_get_ability('smg-site-suite/delete-redirect');
$siteInventoryAbility = wp_get_ability('smg-site-suite/get-site-inventory');
$databaseSizesAbility = wp_get_ability('smg-site-suite/get-database-table-sizes');
$rewriteRulesAbility = wp_get_ability('smg-site-suite/list-rewrite-rules');
$siteHealthAbility = wp_get_ability('smg-site-suite/get-site-health');
$listPresetsAbility = wp_get_ability('smg-site-suite/list-presets');
$applyPresetAbility = wp_get_ability('smg-site-suite/apply-preset');

$originalActive = (array) get_option('smg_site_suite_active_modules', []);
$originalDashboard = get_option('smg_site_suite_custom_dashboard', null);
$originalOwners = get_option('smg_site_suite_protected_owners', null);
$original404Log = get_option('smg_site_suite_404_log', null);
$originalRedirects = get_option('smg_site_suite_redirects', null);
$originalRedirectStats = get_option('smg_site_suite_redirect_stats', null);
$originalRewriteRules = get_option('rewrite_rules', null);
$createdUserIds = [];

try {
    if ($listAbility instanceof WP_Ability) {
        $result = $listAbility->execute([]);
        $assert(!is_wp_error($result), 'List modules ability returned an error.');
        if (is_array($result)) {
            $assert(
                ($result['count'] ?? 0) >= 135,
                'List modules ability returned fewer than 135 modules.'
            );
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
            in_array(
                'reading-time',
                (array) get_option('smg_site_suite_active_modules', []),
                true
            ),
            'Activate module ability did not persist active state.'
        );

        $deactivate = $deactivateAbility->execute(['slug' => 'reading-time']);
        $assert(!is_wp_error($deactivate), 'Deactivate module ability returned an error.');
        $assert(
            !in_array(
                'reading-time',
                (array) get_option('smg_site_suite_active_modules', []),
                true
            ),
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

    if ($systemSummaryAbility instanceof WP_Ability) {
        $blocked = $systemSummaryAbility->execute([]);
        $assert(
            is_wp_error($blocked)
                && $blocked->get_error_code() === 'smg_site_suite_module_inactive',
            'System summary ability did not require its module to be active.'
        );
    }

    if ($activateAbility instanceof WP_Ability) {
        foreach ([
            'system-summary',
            'cron-viewer',
            '404-tracker',
            'redirect-manager',
            'site-inventory-export',
            'database-table-sizes',
            'rewrite-rules-viewer',
            'site-health-extensions',
        ] as $slug) {
            $activated = $activateAbility->execute(['slug' => $slug]);
            $assert(!is_wp_error($activated), "Could not activate operational module: {$slug}");
        }
    }

    if ($systemSummaryAbility instanceof WP_Ability) {
        $summary = $systemSummaryAbility->execute([]);
        $assert(!is_wp_error($summary), 'System summary ability returned an error.');
        if (is_array($summary)) {
            $assert(
                ($summary['wordpress_version'] ?? '') !== '',
                'System summary did not return the WordPress version.'
            );
            $assert(
                ($summary['site_url'] ?? '') === site_url(),
                'System summary returned the wrong site URL.'
            );
        }
    }

    if ($cronAbility instanceof WP_Ability) {
        $cron = $cronAbility->execute(['limit' => 25]);
        $assert(!is_wp_error($cron), 'Cron ability returned an error.');
        if (is_array($cron)) {
            $assert(
                ($cron['count'] ?? -1) <= 25,
                'Cron ability ignored its result limit.'
            );
        }
    }

    update_option(
        'smg_site_suite_404_log',
        [
            md5('http://smg.test/missing-page') => [
                'url' => 'http://smg.test/missing-page',
                'count' => 4,
                'last' => time(),
                'referer' => 'http://smg.test/source',
            ],
        ],
        false
    );

    if ($notFoundAbility instanceof WP_Ability) {
        $notFound = $notFoundAbility->execute(['limit' => 10, 'search' => 'missing']);
        $assert(!is_wp_error($notFound), '404 listing ability returned an error.');
        if (is_array($notFound)) {
            $assert(
                ($notFound['count'] ?? 0) === 1,
                '404 listing ability did not return the seeded entry.'
            );
            $assert(
                ($notFound['entries'][0]['path'] ?? '') === '/missing-page',
                '404 listing ability returned the wrong path.'
            );
        }
    }

    if ($upsertRedirectAbility instanceof WP_Ability) {
        $external = $upsertRedirectAbility->execute([
            'from' => '/bad',
            'to' => 'https://example.com/',
            'code' => 301,
        ]);
        $assert(
            is_wp_error($external),
            'Redirect ability accepted an external destination.'
        );

        $loop = $upsertRedirectAbility->execute([
            'from' => '/same',
            'to' => '/same',
            'code' => 301,
        ]);
        $assert(
            is_wp_error($loop),
            'Redirect ability accepted a direct redirect loop.'
        );

        $created = $upsertRedirectAbility->execute([
            'from' => '/old-page',
            'to' => '/new-page',
            'code' => 308,
        ]);
        $assert(!is_wp_error($created), 'Redirect upsert ability returned an error.');
        if (is_array($created)) {
            $assert(
                ($created['code'] ?? 0) === 308,
                'Redirect upsert ability did not preserve the requested code.'
            );
        }
    }

    if ($listRedirectsAbility instanceof WP_Ability) {
        $redirects = $listRedirectsAbility->execute([]);
        $assert(!is_wp_error($redirects), 'Redirect listing ability returned an error.');
        if (is_array($redirects)) {
            $matching = array_values(array_filter(
                (array) ($redirects['rules'] ?? []),
                static fn(array $row): bool => ($row['from'] ?? '') === '/old-page'
            ));
            $assert(
                count($matching) === 1
                    && ($matching[0]['to'] ?? '') === '/new-page'
                    && ($matching[0]['code'] ?? 0) === 308,
                'Redirect listing ability did not return the created rule.'
            );
        }
    }

    if ($deleteRedirectAbility instanceof WP_Ability) {
        $deleted = $deleteRedirectAbility->execute(['from' => '/old-page']);
        $assert(!is_wp_error($deleted), 'Redirect delete ability returned an error.');
        if (is_array($deleted)) {
            $assert(
                ($deleted['deleted'] ?? false) === true,
                'Redirect delete ability did not report deletion.'
            );
        }

        $deletedAgain = $deleteRedirectAbility->execute(['from' => '/old-page']);
        $assert(!is_wp_error($deletedAgain), 'Repeated redirect deletion returned an error.');
        if (is_array($deletedAgain)) {
            $assert(
                ($deletedAgain['deleted'] ?? true) === false,
                'Repeated redirect deletion was not idempotent.'
            );
        }
    }

    if ($siteInventoryAbility instanceof WP_Ability) {
        $inventory = $siteInventoryAbility->execute([]);
        $assert(!is_wp_error($inventory), 'Site inventory ability returned an error.');
        if (is_array($inventory)) {
            $assert(
                ($inventory['runtime']['wordpress'] ?? '') !== '',
                'Site inventory did not return the WordPress version.'
            );
            $assert(
                isset($inventory['plugins']) && is_array($inventory['plugins']),
                'Site inventory did not return a plugin list.'
            );
            $assert(
                ($inventory['site_suite']['version'] ?? '') === SMG_SITE_SUITE_VERSION,
                'Site inventory returned the wrong Site Suite version.'
            );
        }
    }

    if ($databaseSizesAbility instanceof WP_Ability) {
        $databaseSizes = $databaseSizesAbility->execute(['limit' => 10]);
        $assert(!is_wp_error($databaseSizes), 'Database sizes ability returned an error.');
        if (is_array($databaseSizes)) {
            $assert(
                ($databaseSizes['count'] ?? -1) <= 10,
                'Database sizes ability ignored its result limit.'
            );
            $assert(
                ($databaseSizes['total_bytes'] ?? -1) >= 0,
                'Database sizes ability returned an invalid total.'
            );
        }
    }

    update_option(
        'rewrite_rules',
        [
            '^smg-agent-test/?$' => 'index.php?pagename=smg-agent-test',
            '^unrelated/?$' => 'index.php?pagename=unrelated',
        ],
        false
    );

    if ($rewriteRulesAbility instanceof WP_Ability) {
        $rewriteRules = $rewriteRulesAbility->execute([
            'limit' => 10,
            'search' => 'smg-agent-test',
        ]);
        $assert(!is_wp_error($rewriteRules), 'Rewrite rules ability returned an error.');
        if (is_array($rewriteRules)) {
            $assert(
                ($rewriteRules['stored_count'] ?? 0) === 2,
                'Rewrite rules ability returned the wrong stored rule count.'
            );
            $assert(
                ($rewriteRules['count'] ?? 0) === 1,
                'Rewrite rules ability search did not filter to one rule.'
            );
            $assert(
                ($rewriteRules['rules'][0]['pattern'] ?? '') === '^smg-agent-test/?$',
                'Rewrite rules ability returned the wrong pattern.'
            );
        }
    }

    if ($siteHealthAbility instanceof WP_Ability) {
        $health = $siteHealthAbility->execute([]);
        $assert(!is_wp_error($health), 'Site health ability returned an error.');
        if (is_array($health)) {
            $assert(
                ($health['count'] ?? 0) === 4,
                'Site health ability did not return all four checks.'
            );
            $ids = array_values(array_map(
                static fn(array $check): string => (string) ($check['id'] ?? ''),
                (array) ($health['checks'] ?? [])
            ));
            sort($ids);
            $assert(
                $ids === ['cron', 'debug', 'https', 'search_visibility'],
                'Site health ability returned an unexpected check set.'
            );
        }
    }

    if ($listPresetsAbility instanceof WP_Ability) {
        $presetList=$listPresetsAbility->execute([]);
        $assert(!is_wp_error($presetList),'Preset listing ability returned an error.');

        if(is_array($presetList)){
            $assert(
                ($presetList['count']??0)===7,
                'Preset listing ability returned the wrong preset count.'
            );

            $slugs=array_column((array)($presetList['presets']??[]),'slug');
            $assert(
                in_array('clean-wordpress',$slugs,true),
                'Preset listing ability is missing Clean WordPress.'
            );
        }
    }

    if($applyPresetAbility instanceof WP_Ability){
        $unknown=$applyPresetAbility->execute(['preset'=>'not-a-preset']);
        $assert(
            is_wp_error($unknown),
            'Apply preset ability accepted an unknown preset.'
        );

        $applied=$applyPresetAbility->execute(['preset'=>'clean-wordpress']);
        $assert(!is_wp_error($applied),'Apply preset ability returned an error.');

        if(is_array($applied)){
            $assert(
                ($applied['preset']??'')==='clean-wordpress',
                'Apply preset ability returned the wrong preset slug.'
            );
            $expected=[
                'disable-emojis',
                'disable-embeds',
                'disable-xml-rpc',
                'disable-dashicons-frontend',
                'clean-wp-head',
                'disable-admin-bar-frontend',
            ];
            $active=(array)get_option('smg_site_suite_active_modules',[]);
            foreach($expected as $slug){
                $assert(
                    in_array($slug,$active,true),
                    "Apply preset ability did not activate: {$slug}"
                );
            }
        }

        $secondApply=$applyPresetAbility->execute(['preset'=>'clean-wordpress']);
        $assert(!is_wp_error($secondApply),'Repeated preset application returned an error.');
        if(is_array($secondApply)){
            $assert(
                ($secondApply['activated']??[])===[],
                'Repeated preset application was not idempotent.'
            );
            $assert(
                count((array)($secondApply['already_active']??[]))===6,
                'Repeated preset application did not report already-active modules.'
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

            if ($upsertRedirectAbility instanceof WP_Ability) {
                $blockedWrite = $upsertRedirectAbility->execute([
                    'from' => '/blocked',
                    'to' => '/target',
                    'code' => 301,
                ]);
                $assert(
                    is_wp_error($blockedWrite),
                    'Protected Owner did not block a mutating operational ability.'
                );
            }
        }
    }
} finally {
    wp_set_current_user($admin->ID);
    update_option('smg_site_suite_active_modules', $originalActive, false);

    $restore = static function (string $option, $value): void {
        if ($value === null) {
            delete_option($option);
            return;
        }
        update_option($option, $value, false);
    };

    $restore('smg_site_suite_custom_dashboard', $originalDashboard);
    $restore('smg_site_suite_protected_owners', $originalOwners);
    $restore('smg_site_suite_404_log', $original404Log);
    $restore('smg_site_suite_redirects', $originalRedirects);
    $restore('smg_site_suite_redirect_stats', $originalRedirectStats);
    $restore('rewrite_rules', $originalRewriteRules);

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
