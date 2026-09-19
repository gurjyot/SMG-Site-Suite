<?php
namespace SMG\SiteSuite\Agent;

use SMG\SiteSuite\Modules\Admin\ProtectedOwner;
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
