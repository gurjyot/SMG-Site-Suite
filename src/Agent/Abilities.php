<?php
namespace SMG\SiteSuite\Agent;

use SMG\SiteSuite\Modules\Admin\CronViewer;
use SMG\SiteSuite\Modules\Admin\DatabaseTableSizes;
use SMG\SiteSuite\Modules\Admin\ProtectedOwner;
use SMG\SiteSuite\Modules\Admin\SystemSummary;
use SMG\SiteSuite\Modules\Admin\RewriteRulesViewer;
use SMG\SiteSuite\Modules\Admin\SiteInventoryExport;
use SMG\SiteSuite\Modules\Utilities\NotFoundTracker;
use SMG\SiteSuite\Modules\Utilities\RedirectManager;
use SMG\WPFoundation\Modules\ModuleDefinition;
use SMG\WPFoundation\Modules\ModuleManager;
use Throwable;
use WP_Error;

final class Abilities {
    private const CATEGORY = 'smg-site-suite';

    public function __construct(private ModuleManager $manager) {}

    public function boot(): void {
        add_action('wp_abilities_api_categories_init', [$this, 'registerCategory']);
        add_action('wp_abilities_api_init', [$this, 'registerAbilities']);
    }

    public function registerCategory(): void {
        if (!function_exists('wp_register_ability_category')) {
            return;
        }

        wp_register_ability_category(
            self::CATEGORY,
            [
                'label' => __('SMG Site Suite', 'smg-site-suite'),
                'description' => __(
                    'Discover and manage SMG Site Suite modules through WordPress Abilities.',
                    'smg-site-suite'
                ),
            ]
        );
    }

    public function registerAbilities(): void {
        if (!function_exists('wp_register_ability')) {
            return;
        }

        wp_register_ability(
            'smg-site-suite/list-modules',
            [
                'label' => __('List Site Suite Modules', 'smg-site-suite'),
                'description' => __(
                    'Lists Site Suite modules and their current activation and dependency status.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'category' => ['type' => 'string'],
                        'active' => ['type' => 'boolean'],
                        'search' => ['type' => 'string'],
                    ],
                    'additionalProperties' => false,
                ],
                'output_schema' => $this->moduleListSchema(),
                'execute_callback' => [$this, 'listModules'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/get-module',
            [
                'label' => __('Get Site Suite Module', 'smg-site-suite'),
                'description' => __(
                    'Returns metadata and current status for one Site Suite module.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->slugInputSchema(),
                'output_schema' => $this->singleModuleSchema(),
                'execute_callback' => [$this, 'getModule'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/activate-module',
            [
                'label' => __('Activate Site Suite Module', 'smg-site-suite'),
                'description' => __(
                    'Activates one Site Suite module after checking its declared dependencies.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->slugInputSchema(),
                'output_schema' => $this->singleModuleSchema(),
                'execute_callback' => [$this, 'activateModule'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(false, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/deactivate-module',
            [
                'label' => __('Deactivate Site Suite Module', 'smg-site-suite'),
                'description' => __(
                    'Deactivates one Site Suite module using its normal lifecycle contract.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->slugInputSchema(),
                'output_schema' => $this->singleModuleSchema(),
                'execute_callback' => [$this, 'deactivateModule'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(false, true, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/get-module-settings',
            [
                'label' => __('Get Site Suite Module Settings', 'smg-site-suite'),
                'description' => __(
                    'Returns the settings schema and current settings for a configurable Site Suite module.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->slugInputSchema(),
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'slug' => ['type' => 'string'],
                        'schema' => ['type' => 'object'],
                        'settings' => ['type' => 'object'],
                    ],
                    'required' => ['slug', 'schema', 'settings'],
                    'additionalProperties' => false,
                ],
                'execute_callback' => [$this, 'getModuleSettings'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/update-module-settings',
            [
                'label' => __('Update Site Suite Module Settings', 'smg-site-suite'),
                'description' => __(
                    'Updates one configurable Site Suite module through its existing settings sanitizer.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'slug' => [
                            'type' => 'string',
                            'pattern' => '^[a-z0-9]+(?:-[a-z0-9]+)*$',
                        ],
                        'settings' => ['type' => 'object'],
                    ],
                    'required' => ['slug', 'settings'],
                    'additionalProperties' => false,
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'slug' => ['type' => 'string'],
                        'settings' => ['type' => 'object'],
                    ],
                    'required' => ['slug', 'settings'],
                    'additionalProperties' => false,
                ],
                'execute_callback' => [$this, 'updateModuleSettings'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(false, true, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/get-system-summary',
            [
                'label' => __('Get Site System Summary', 'smg-site-suite'),
                'description' => __(
                    'Returns structured WordPress, PHP, database, theme, URL, timezone, memory, multisite, and debug information.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->emptyInputSchema(),
                'output_schema' => $this->systemSummarySchema(),
                'execute_callback' => [$this, 'getSystemSummary'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/list-cron-events',
            [
                'label' => __('List WordPress Cron Events', 'smg-site-suite'),
                'description' => __(
                    'Lists scheduled WordPress cron events from the active Cron Viewer module.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 500,
                        ],
                        'hook' => ['type' => 'string'],
                    ],
                    'additionalProperties' => false,
                ],
                'output_schema' => $this->cronListSchema(),
                'execute_callback' => [$this, 'listCronEvents'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/list-404s',
            [
                'label' => __('List Tracked 404s', 'smg-site-suite'),
                'description' => __(
                    'Lists recent tracked 404 requests from the active 404 Tracker module.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 500,
                        ],
                        'search' => ['type' => 'string'],
                    ],
                    'additionalProperties' => false,
                ],
                'output_schema' => $this->notFoundListSchema(),
                'execute_callback' => [$this, 'listNotFoundEntries'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/list-redirects',
            [
                'label' => __('List Redirect Rules', 'smg-site-suite'),
                'description' => __(
                    'Lists local redirect rules and usage statistics from the active Redirect Manager module.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->emptyInputSchema(),
                'output_schema' => $this->redirectListSchema(),
                'execute_callback' => [$this, 'listRedirects'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/upsert-redirect',
            [
                'label' => __('Create or Update Redirect', 'smg-site-suite'),
                'description' => __(
                    'Creates or replaces one local redirect rule through the active Redirect Manager module.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->redirectInputSchema(true),
                'output_schema' => $this->redirectRuleSchema(),
                'execute_callback' => [$this, 'upsertRedirect'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(false, true, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/delete-redirect',
            [
                'label' => __('Delete Redirect', 'smg-site-suite'),
                'description' => __(
                    'Deletes one local redirect rule through the active Redirect Manager module.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->redirectInputSchema(false),
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'from' => ['type' => 'string'],
                        'deleted' => ['type' => 'boolean'],
                    ],
                    'required' => ['from', 'deleted'],
                    'additionalProperties' => false,
                ],
                'execute_callback' => [$this, 'deleteRedirect'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(false, true, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/get-site-inventory',
            [
                'label' => __('Get Site Inventory', 'smg-site-suite'),
                'description' => __(
                    'Returns a structured inventory of the WordPress runtime, theme, installed plugins, and active Site Suite modules.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => $this->emptyInputSchema(),
                'output_schema' => $this->siteInventorySchema(),
                'execute_callback' => [$this, 'getSiteInventory'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/get-database-table-sizes',
            [
                'label' => __('Get Database Table Sizes', 'smg-site-suite'),
                'description' => __(
                    'Returns a bounded read-only overview of WordPress database table sizes.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 500,
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'output_schema' => $this->databaseTableSizesSchema(),
                'execute_callback' => [$this, 'getDatabaseTableSizes'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );

        wp_register_ability(
            'smg-site-suite/list-rewrite-rules',
            [
                'label' => __('List Rewrite Rules', 'smg-site-suite'),
                'description' => __(
                    'Returns a bounded read-only list of stored WordPress rewrite rules.',
                    'smg-site-suite'
                ),
                'category' => self::CATEGORY,
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 1000,
                        ],
                        'search' => ['type' => 'string'],
                    ],
                    'additionalProperties' => false,
                ],
                'output_schema' => $this->rewriteRulesSchema(),
                'execute_callback' => [$this, 'listRewriteRules'],
                'permission_callback' => [$this, 'canManageSiteSuite'],
                'meta' => $this->meta(true, false, true),
            ]
        );
    }

    public function canManageSiteSuite(): bool {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if (ProtectedOwner::isEnabled() && !ProtectedOwner::isProtectedCurrentUser()) {
            return false;
        }

        return true;
    }

    public function listModules($input = null): array {
        $input = is_array($input) ? $input : [];
        $category = isset($input['category']) ? sanitize_key((string) $input['category']) : '';
        $search = isset($input['search']) ? sanitize_text_field((string) $input['search']) : '';
        $activeFilter = array_key_exists('active', $input) ? (bool) $input['active'] : null;
        $modules = [];

        foreach ($this->manager->registry()->all() as $definition) {
            if (!$definition instanceof ModuleDefinition) {
                continue;
            }

            $module = $this->modulePayload($definition);
            if ($category !== '' && $module['category'] !== $category) {
                continue;
            }
            if ($activeFilter !== null && $module['active'] !== $activeFilter) {
                continue;
            }
            if ($search !== '' && !$this->matchesSearch($module, $search)) {
                continue;
            }

            $modules[] = $module;
        }

        return [
            'count' => count($modules),
            'modules' => $modules,
        ];
    }

    public function getModule(array $input) {
        $definition = $this->definitionFromInput($input);
        if (is_wp_error($definition)) {
            return $definition;
        }

        return ['module' => $this->modulePayload($definition)];
    }

    public function activateModule(array $input) {
        $definition = $this->definitionFromInput($input);
        if (is_wp_error($definition)) {
            return $definition;
        }

        try {
            $this->manager->activate($definition->slug());
        } catch (Throwable $error) {
            return new WP_Error(
                'smg_site_suite_activation_failed',
                $error->getMessage()
            );
        }

        return ['module' => $this->modulePayload($definition)];
    }

    public function deactivateModule(array $input) {
        $definition = $this->definitionFromInput($input);
        if (is_wp_error($definition)) {
            return $definition;
        }

        try {
            $this->manager->deactivate($definition->slug());
        } catch (Throwable $error) {
            return new WP_Error(
                'smg_site_suite_deactivation_failed',
                $error->getMessage()
            );
        }

        return ['module' => $this->modulePayload($definition)];
    }

    public function getModuleSettings(array $input) {
        $definition = $this->definitionFromInput($input);
        if (is_wp_error($definition)) {
            return $definition;
        }

        try {
            $module = $this->manager->settingsModule($definition->slug());
        } catch (Throwable $error) {
            return new WP_Error(
                'smg_site_suite_settings_unavailable',
                $error->getMessage()
            );
        }

        return [
            'slug' => $definition->slug(),
            'schema' => $module->settingsSchema(),
            'settings' => $module->settings(),
        ];
    }

    public function updateModuleSettings(array $input) {
        $definition = $this->definitionFromInput($input);
        if (is_wp_error($definition)) {
            return $definition;
        }

        $settings = isset($input['settings']) && is_array($input['settings'])
            ? $input['settings']
            : [];

        try {
            $module = $this->manager->settingsModule($definition->slug());
            $module->saveSettings($settings);
        } catch (Throwable $error) {
            return new WP_Error(
                'smg_site_suite_settings_update_failed',
                $error->getMessage()
            );
        }

        return [
            'slug' => $definition->slug(),
            'settings' => $module->settings(),
        ];
    }

    public function getSystemSummary() {
        $active = $this->requireActiveModule('system-summary');
        if (is_wp_error($active)) {
            return $active;
        }

        return (new SystemSummary())->summary();
    }

    public function listCronEvents($input = null) {
        $active = $this->requireActiveModule('cron-viewer');
        if (is_wp_error($active)) {
            return $active;
        }

        $input = is_array($input) ? $input : [];
        $limit = isset($input['limit']) ? absint($input['limit']) : 100;
        $limit = max(1, min(500, $limit));
        $hook = isset($input['hook'])
            ? sanitize_text_field((string) $input['hook'])
            : '';

        $events = (new CronViewer())->events(500);
        if ($hook !== '') {
            $events = array_values(array_filter(
                $events,
                static fn(array $event): bool =>
                    stripos((string) ($event['hook'] ?? ''), $hook) !== false
            ));
        }

        $events = array_slice($events, 0, $limit);

        return [
            'count' => count($events),
            'events' => $events,
        ];
    }

    public function listNotFoundEntries($input = null) {
        $active = $this->requireActiveModule('404-tracker');
        if (is_wp_error($active)) {
            return $active;
        }

        $input = is_array($input) ? $input : [];
        $limit = isset($input['limit']) ? absint($input['limit']) : 100;
        $limit = max(1, min(500, $limit));
        $search = isset($input['search'])
            ? sanitize_text_field((string) $input['search'])
            : '';

        $entries = (new NotFoundTracker())->entries(500);
        if ($search !== '') {
            $entries = array_values(array_filter(
                $entries,
                static function (array $entry) use ($search): bool {
                    $haystack = implode(' ', [
                        (string) ($entry['url'] ?? ''),
                        (string) ($entry['path'] ?? ''),
                        (string) ($entry['referrer'] ?? ''),
                    ]);
                    return stripos($haystack, $search) !== false;
                }
            ));
        }

        $entries = array_slice($entries, 0, $limit);

        return [
            'count' => count($entries),
            'entries' => $entries,
        ];
    }

    public function listRedirects() {
        $active = $this->requireActiveModule('redirect-manager');
        if (is_wp_error($active)) {
            return $active;
        }

        $rules = (new RedirectManager())->rulesWithStats();

        return [
            'count' => count($rules),
            'rules' => $rules,
        ];
    }

    public function upsertRedirect(array $input) {
        $active = $this->requireActiveModule('redirect-manager');
        if (is_wp_error($active)) {
            return $active;
        }

        return (new RedirectManager())->upsertRule(
            (string) ($input['from'] ?? ''),
            (string) ($input['to'] ?? ''),
            isset($input['code']) ? absint($input['code']) : 301
        );
    }

    public function deleteRedirect(array $input) {
        $active = $this->requireActiveModule('redirect-manager');
        if (is_wp_error($active)) {
            return $active;
        }

        $from = sanitize_text_field((string) ($input['from'] ?? ''));
        $result = (new RedirectManager())->deleteRule($from);

        if (is_wp_error($result)) {
            if ($result->get_error_code() === 'smg_site_suite_redirect_not_found') {
                return [
                    'from' => $from,
                    'deleted' => false,
                ];
            }
            return $result;
        }

        return [
            'from' => $from,
            'deleted' => true,
        ];
    }

    public function getSiteInventory() {
        $active = $this->requireActiveModule('site-inventory-export');
        if (is_wp_error($active)) {
            return $active;
        }

        return (new SiteInventoryExport())->inventory();
    }

    public function getDatabaseTableSizes($input = null) {
        $active = $this->requireActiveModule('database-table-sizes');
        if (is_wp_error($active)) {
            return $active;
        }

        $input = is_array($input) ? $input : [];
        $limit = isset($input['limit']) ? absint($input['limit']) : 100;

        return (new DatabaseTableSizes())->tables($limit);
    }

    public function listRewriteRules($input = null) {
        $active = $this->requireActiveModule('rewrite-rules-viewer');
        if (is_wp_error($active)) {
            return $active;
        }

        $input = is_array($input) ? $input : [];
        $limit = isset($input['limit']) ? absint($input['limit']) : 100;
        $search = isset($input['search'])
            ? sanitize_text_field((string) $input['search'])
            : '';

        return (new RewriteRulesViewer())->rules($limit, $search);
    }

    private function requireActiveModule(string $slug) {
        $status = $this->manager->status($slug);

        if (!$status['known']) {
            return new WP_Error(
                'smg_site_suite_unknown_module',
                __('Required Site Suite module is not registered.', 'smg-site-suite')
            );
        }

        if (!$status['active']) {
            return new WP_Error(
                'smg_site_suite_module_inactive',
                sprintf(
                    /* translators: %s: Site Suite module slug. */
                    __('The %s module must be active before using this ability.', 'smg-site-suite'),
                    $slug
                )
            );
        }

        if (!$status['available']) {
            return new WP_Error(
                'smg_site_suite_module_unavailable',
                __('The required Site Suite module has missing dependencies.', 'smg-site-suite')
            );
        }

        return true;
    }

    private function definitionFromInput(array $input) {
        $slug = isset($input['slug']) ? sanitize_key((string) $input['slug']) : '';
        $definition = $this->manager->registry()->get($slug);

        if (!$definition instanceof ModuleDefinition) {
            return new WP_Error(
                'smg_site_suite_unknown_module',
                __('Unknown Site Suite module.', 'smg-site-suite')
            );
        }

        return $definition;
    }

    private function modulePayload(ModuleDefinition $definition): array {
        $status = $this->manager->status($definition->slug());

        return [
            'slug' => $definition->slug(),
            'name' => $definition->name(),
            'description' => $definition->description(),
            'category' => $definition->category(),
            'tags' => $definition->tags(),
            'contexts' => $definition->contexts(),
            'risk' => $definition->risk(),
            'has_settings' => $definition->hasSettings(),
            'active' => (bool) $status['active'],
            'available' => (bool) $status['available'],
            'missing_dependencies' => array_values(array_map('strval', (array) $status['missing'])),
        ];
    }

    private function matchesSearch(array $module, string $search): bool {
        $haystack = implode(
            ' ',
            [
                $module['slug'],
                $module['name'],
                $module['description'],
                implode(' ', $module['tags']),
            ]
        );

        return stripos($haystack, $search) !== false;
    }

    private function emptyInputSchema(): array {
        return [
            'type' => 'object',
            'additionalProperties' => false,
        ];
    }

    private function systemSummarySchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'wordpress_version' => ['type' => 'string'],
                'php_version' => ['type' => 'string'],
                'database_version' => ['type' => 'string'],
                'theme_name' => ['type' => 'string'],
                'theme_version' => ['type' => 'string'],
                'site_url' => ['type' => 'string'],
                'home_url' => ['type' => 'string'],
                'timezone' => ['type' => 'string'],
                'memory_limit' => ['type' => 'string'],
                'multisite' => ['type' => 'boolean'],
                'debug' => ['type' => 'boolean'],
            ],
            'required' => [
                'wordpress_version',
                'php_version',
                'database_version',
                'theme_name',
                'theme_version',
                'site_url',
                'home_url',
                'timezone',
                'memory_limit',
                'multisite',
                'debug',
            ],
            'additionalProperties' => false,
        ];
    }

    private function cronListSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'count' => ['type' => 'integer'],
                'events' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'timestamp' => ['type' => 'integer'],
                            'next_run' => ['type' => 'string'],
                            'hook' => ['type' => 'string'],
                            'schedule' => ['type' => 'string'],
                            'args' => ['type' => 'array'],
                        ],
                        'required' => ['timestamp', 'next_run', 'hook', 'schedule', 'args'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['count', 'events'],
            'additionalProperties' => false,
        ];
    }

    private function notFoundListSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'count' => ['type' => 'integer'],
                'entries' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'url' => ['type' => 'string'],
                            'path' => ['type' => 'string'],
                            'hits' => ['type' => 'integer'],
                            'last_seen' => ['type' => 'integer'],
                            'last_seen_iso' => ['type' => 'string'],
                            'referrer' => ['type' => 'string'],
                        ],
                        'required' => [
                            'url',
                            'path',
                            'hits',
                            'last_seen',
                            'last_seen_iso',
                            'referrer',
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['count', 'entries'],
            'additionalProperties' => false,
        ];
    }

    private function redirectInputSchema(bool $includeTarget): array {
        $properties = [
            'from' => ['type' => 'string'],
        ];
        $required = ['from'];

        if ($includeTarget) {
            $properties['to'] = ['type' => 'string'];
            $properties['code'] = [
                'type' => 'integer',
                'enum' => [301, 302, 307, 308],
            ];
            $required[] = 'to';
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
        ];
    }

    private function redirectRuleSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'from' => ['type' => 'string'],
                'to' => ['type' => 'string'],
                'code' => ['type' => 'integer'],
            ],
            'required' => ['from', 'to', 'code'],
            'additionalProperties' => false,
        ];
    }

    private function redirectListSchema(): array {
        $rule = $this->redirectRuleSchema();
        $rule['properties']['hits'] = ['type' => 'integer'];
        $rule['properties']['last_used'] = ['type' => 'integer'];
        $rule['properties']['last_used_iso'] = ['type' => 'string'];
        $rule['required'][] = 'hits';
        $rule['required'][] = 'last_used';
        $rule['required'][] = 'last_used_iso';

        return [
            'type' => 'object',
            'properties' => [
                'count' => ['type' => 'integer'],
                'rules' => [
                    'type' => 'array',
                    'items' => $rule,
                ],
            ],
            'required' => ['count', 'rules'],
            'additionalProperties' => false,
        ];
    }

    private function siteInventorySchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'schema' => ['type' => 'integer'],
                'generated_at' => ['type' => 'string'],
                'site' => [
                    'type' => 'object',
                    'properties' => [
                        'home' => ['type' => 'string'],
                        'site' => ['type' => 'string'],
                        'multisite' => ['type' => 'boolean'],
                        'timezone' => ['type' => 'string'],
                    ],
                    'required' => ['home', 'site', 'multisite', 'timezone'],
                    'additionalProperties' => false,
                ],
                'runtime' => [
                    'type' => 'object',
                    'properties' => [
                        'wordpress' => ['type' => 'string'],
                        'php' => ['type' => 'string'],
                        'database' => ['type' => 'string'],
                        'memory_limit' => ['type' => 'string'],
                    ],
                    'required' => ['wordpress', 'php', 'database', 'memory_limit'],
                    'additionalProperties' => false,
                ],
                'theme' => [
                    'type' => 'object',
                    'properties' => [
                        'stylesheet' => ['type' => 'string'],
                        'name' => ['type' => 'string'],
                        'version' => ['type' => 'string'],
                    ],
                    'required' => ['stylesheet', 'name', 'version'],
                    'additionalProperties' => false,
                ],
                'plugins' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'file' => ['type' => 'string'],
                            'name' => ['type' => 'string'],
                            'version' => ['type' => 'string'],
                            'active' => ['type' => 'boolean'],
                        ],
                        'required' => ['file', 'name', 'version', 'active'],
                        'additionalProperties' => false,
                    ],
                ],
                'site_suite' => [
                    'type' => 'object',
                    'properties' => [
                        'version' => ['type' => 'string'],
                        'active_modules' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                    ],
                    'required' => ['version', 'active_modules'],
                    'additionalProperties' => false,
                ],
            ],
            'required' => [
                'schema',
                'generated_at',
                'site',
                'runtime',
                'theme',
                'plugins',
                'site_suite',
            ],
            'additionalProperties' => false,
        ];
    }

    private function databaseTableSizesSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'count' => ['type' => 'integer'],
                'total_bytes' => ['type' => 'integer'],
                'tables' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'rows' => ['type' => 'integer'],
                            'data_bytes' => ['type' => 'integer'],
                            'index_bytes' => ['type' => 'integer'],
                            'total_bytes' => ['type' => 'integer'],
                        ],
                        'required' => [
                            'name',
                            'rows',
                            'data_bytes',
                            'index_bytes',
                            'total_bytes',
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['count', 'total_bytes', 'tables'],
            'additionalProperties' => false,
        ];
    }

    private function rewriteRulesSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'stored_count' => ['type' => 'integer'],
                'count' => ['type' => 'integer'],
                'rules' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'pattern' => ['type' => 'string'],
                            'target' => ['type' => 'string'],
                        ],
                        'required' => ['pattern', 'target'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['stored_count', 'count', 'rules'],
            'additionalProperties' => false,
        ];
    }

    private function slugInputSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'slug' => [
                    'type' => 'string',
                    'pattern' => '^[a-z0-9]+(?:-[a-z0-9]+)*$',
                ],
            ],
            'required' => ['slug'],
            'additionalProperties' => false,
        ];
    }

    private function moduleListSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'count' => ['type' => 'integer'],
                'modules' => [
                    'type' => 'array',
                    'items' => $this->moduleSchema(),
                ],
            ],
            'required' => ['count', 'modules'],
            'additionalProperties' => false,
        ];
    }

    private function singleModuleSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'module' => $this->moduleSchema(),
            ],
            'required' => ['module'],
            'additionalProperties' => false,
        ];
    }

    private function moduleSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'slug' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'category' => ['type' => 'string'],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'contexts' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'risk' => ['type' => 'string'],
                'has_settings' => ['type' => 'boolean'],
                'active' => ['type' => 'boolean'],
                'available' => ['type' => 'boolean'],
                'missing_dependencies' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => [
                'slug',
                'name',
                'description',
                'category',
                'tags',
                'contexts',
                'risk',
                'has_settings',
                'active',
                'available',
                'missing_dependencies',
            ],
            'additionalProperties' => false,
        ];
    }

    private function meta(bool $readonly, bool $destructive, bool $idempotent): array {
        return [
            'show_in_rest' => true,
            'annotations' => [
                'readonly' => $readonly,
                'destructive' => $destructive,
                'idempotent' => $idempotent,
            ],
            'mcp' => [
                'public' => true,
            ],
        ];
    }
}
