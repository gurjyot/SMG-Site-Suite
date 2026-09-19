<?php
namespace SMG\WPFoundation\Modules;

use RuntimeException;
use SMG\WPFoundation\Contracts\ActivatableModuleInterface;
use SMG\WPFoundation\Contracts\DeactivatableModuleInterface;
use SMG\WPFoundation\Contracts\ModuleInterface;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class ModuleManager {
    private ModuleRegistry $registry;
    private ModuleStateStore $state;
    private DependencyChecker $dependencies;

    public function __construct(
        ModuleRegistry $registry,
        ModuleStateStore $state,
        DependencyChecker $dependencies
    ) {
        $this->registry = $registry;
        $this->state = $state;
        $this->dependencies = $dependencies;
    }

    public function registry(): ModuleRegistry {
        return $this->registry;
    }

    public function state(): ModuleStateStore {
        return $this->state;
    }

    public function status(string $slug): array {
        $module = $this->registry->get($slug);

        if (!$module) {
            return [
                'known' => false,
                'active' => false,
                'available' => false,
                'missing' => [],
            ];
        }

        $dependencies = $this->dependencies->check($module);

        return [
            'known' => true,
            'active' => $this->state->isActive($slug),
            'available' => $dependencies['available'],
            'missing' => $dependencies['missing'],
        ];
    }

    public function activate(string $slug): void {
        $module = $this->requireDefinition($slug);
        $dependencies = $this->dependencies->check($module);

        if (!$dependencies['available']) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
            throw new RuntimeException(
                'Cannot activate module; missing dependencies: '.implode(', ', $dependencies['missing'])
            );
        }

        $instance = $this->instance($module);
        if ($instance instanceof ActivatableModuleInterface) {
            $instance->activate();
        }

        $this->state->activate($slug);
        do_action('smg_wp_foundation_module_activated', $slug);
    }

    public function deactivate(string $slug): void {
        $module = $this->requireDefinition($slug);
        $instance = $this->instance($module);

        if ($instance instanceof DeactivatableModuleInterface) {
            $instance->deactivate();
        }

        $this->state->deactivate($slug);
        do_action('smg_wp_foundation_module_deactivated', $slug);
    }

    public function settingsModule(string $slug): SettingsModuleInterface {
        $module = $this->requireDefinition($slug);

        if (!$module->hasSettings()) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
            throw new RuntimeException('Module has no settings contract.');
        }

        $instance = $this->instance($module);
        if (!$instance instanceof SettingsModuleInterface) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
            throw new RuntimeException(
                'Module metadata claims settings support but class does not implement SettingsModuleInterface.'
            );
        }

        return $instance;
    }

    private function requireDefinition(string $slug): ModuleDefinition {
        $module = $this->registry->get($slug);
        if (!$module) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
            throw new RuntimeException('Unknown module: '.$slug);
        }

        return $module;
    }

    private function instance(ModuleDefinition $definition): ModuleInterface {
        $class = $definition->className();
        if (!class_exists($class)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
            throw new RuntimeException('Module class not found: '.$class);
        }

        $instance = new $class();
        if (!$instance instanceof ModuleInterface) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
            throw new RuntimeException('Module must implement ModuleInterface: '.$class);
        }

        return $instance;
    }
}
