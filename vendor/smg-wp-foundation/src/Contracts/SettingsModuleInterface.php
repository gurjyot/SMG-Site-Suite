<?php
namespace SMG\WPFoundation\Contracts;
interface SettingsModuleInterface extends ModuleInterface {
    public function settingsSchema(): array;
    public function settings(): array;
    public function saveSettings(array $input): void;
}
