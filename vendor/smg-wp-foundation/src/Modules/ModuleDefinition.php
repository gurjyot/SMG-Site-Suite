<?php
namespace SMG\WPFoundation\Modules;

use InvalidArgumentException;

final class ModuleDefinition {
    private string $slug;
    private string $name;
    private string $description;
    private string $category;
    private string $className;
    private array $tags;
    private array $dependencies;
    private array $contexts;
    private string $risk;
    private bool $hasSettings;
    private bool $defaultEnabled;
    private string $settingsMode;

    public function __construct(array $definition) {
        foreach (['slug', 'name', 'description', 'category', 'class'] as $required) {
            if (
                !isset($definition[$required])
                || !is_string($definition[$required])
                || trim($definition[$required]) === ''
            ) {
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
                throw new InvalidArgumentException(
                    'Missing or invalid module definition field: '.$required
                );
            }
        }

        $slug = strtolower(trim($definition['slug']));
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
            throw new InvalidArgumentException('Module slug must be lowercase kebab-case.');
        }

        $risk = strtolower((string)($definition['risk'] ?? 'low'));
        if (!in_array($risk, ['low', 'medium', 'high', 'destructive'], true)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal diagnostics, not HTML output.
            throw new InvalidArgumentException('Unsupported module risk level.');
        }

        $settingsMode = strtolower((string)($definition['settings_mode'] ?? 'drawer'));

        $this->slug = $slug;
        $this->name = trim($definition['name']);
        $this->description = trim($definition['description']);
        $this->category = strtolower(trim($definition['category']));
        $this->className = trim($definition['class']);
        $this->tags = $this->stringList($definition['tags'] ?? []);
        $this->dependencies = is_array($definition['dependencies'] ?? null)
            ? $definition['dependencies']
            : [];
        $this->contexts = $this->stringList($definition['contexts'] ?? ['all']);
        $this->risk = $risk;
        $this->hasSettings = (bool)($definition['has_settings'] ?? false);
        $this->defaultEnabled = (bool)($definition['default_enabled'] ?? false);
        $this->settingsMode = in_array($settingsMode, ['drawer', 'page'], true)
            ? $settingsMode
            : 'drawer';
    }

    public function slug(): string {
        return $this->slug;
    }

    public function name(): string {
        return $this->name;
    }

    public function description(): string {
        return $this->description;
    }

    public function category(): string {
        return $this->category;
    }

    public function className(): string {
        return $this->className;
    }

    public function tags(): array {
        return $this->tags;
    }

    public function dependencies(): array {
        return $this->dependencies;
    }

    public function contexts(): array {
        return $this->contexts;
    }

    public function risk(): string {
        return $this->risk;
    }

    public function hasSettings(): bool {
        return $this->hasSettings;
    }

    public function defaultEnabled(): bool {
        return $this->defaultEnabled;
    }

    public function settingsMode(): string {
        return $this->settingsMode;
    }

    public function toArray(): array {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'class' => $this->className,
            'tags' => $this->tags,
            'dependencies' => $this->dependencies,
            'contexts' => $this->contexts,
            'risk' => $this->risk,
            'has_settings' => $this->hasSettings,
            'default_enabled' => $this->defaultEnabled,
            'settings_mode' => $this->settingsMode,
        ];
    }

    private function stringList(array $values): array {
        return array_values(
            array_unique(
                array_filter(array_map('strval', $values))
            )
        );
    }
}
